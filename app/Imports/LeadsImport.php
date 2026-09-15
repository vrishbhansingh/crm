<?php

namespace App\Imports;

use App\Models\Lead;
use App\Models\User;
use App\Services\LeadNumberService;
use App\Services\LeadUniquenessService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

/**
 * Row-by-row Lead bulk import — every row gets a Success/Skipped/Failed
 * verdict and a human-readable reason (never a single all-or-nothing
 * result), duplicate detection runs against both the database and every
 * row already processed earlier in the same file, and one bad row can no
 * longer abort the rest of the import (it's caught and reported as Failed
 * instead of throwing).
 */
class LeadsImport implements ToCollection, WithHeadingRow
{
    /** @var array<int, array{row:int,name:?string,email:?string,phone:?string,status:string,reason:?string}> */
    public array $results = [];

    private int $totalRows = 0;

    private int $successCount = 0;

    private int $skippedCount = 0;

    private int $failedCount = 0;

    /** Normalized email/phone => the first Excel row that used it, so later rows in the same file can be caught as duplicates of it. */
    private array $seenEmails = [];

    private array $seenPhones = [];

    public function __construct(private readonly int $tenantId) {}

    public function collection(Collection $rows)
    {
        foreach ($rows as $i => $row) {
            // WithHeadingRow treats row 1 as the header, so the first data
            // row ($i === 0) is Excel row 2 — reporting this instead of the
            // raw collection index is what lets a user actually find the
            // row in their spreadsheet.
            $excelRow = $i + 2;
            $this->totalRows++;

            $name = trim((string) ($row['name'] ?? ''));
            $email = trim((string) ($row['email'] ?? ''));
            $phone = trim((string) ($row['phone'] ?? ''));

            $result = [
                'row' => $excelRow,
                'name' => $name !== '' ? $name : null,
                'email' => $email !== '' ? $email : null,
                'phone' => $phone !== '' ? $phone : null,
                'status' => 'Failed',
                'reason' => null,
            ];

            $missing = [];
            if ($name === '') {
                $missing[] = 'Name';
            }
            if ($email === '') {
                $missing[] = 'Email';
            }
            if ($phone === '') {
                $missing[] = 'Phone number';
            }
            if ($missing) {
                $result['reason'] = implode(', ', $missing).' '.(count($missing) > 1 ? 'are' : 'is').' required';
                $this->fail($result);

                continue;
            }

            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $result['reason'] = 'Invalid email format';
                $this->fail($result);

                continue;
            }

            $emailNormalized = LeadUniquenessService::normalizeEmail($email);
            $phoneNormalized = LeadUniquenessService::normalizePhone($phone);

            if (! $phoneNormalized) {
                $result['reason'] = 'Invalid phone number';
                $this->fail($result);

                continue;
            }

            if (isset($this->seenEmails[$emailNormalized])) {
                $result['status'] = 'Skipped';
                $result['reason'] = 'Duplicate record found in uploaded file (same email as row '.$this->seenEmails[$emailNormalized].')';
                $this->skip($result);

                continue;
            }
            if (isset($this->seenPhones[$phoneNormalized])) {
                $result['status'] = 'Skipped';
                $result['reason'] = 'Duplicate record found in uploaded file (same phone number as row '.$this->seenPhones[$phoneNormalized].')';
                $this->skip($result);

                continue;
            }

            $duplicate = LeadUniquenessService::findDuplicate($emailNormalized, $phoneNormalized);
            if ($duplicate) {
                $field = LeadUniquenessService::duplicateField($duplicate, $emailNormalized, $phoneNormalized);
                $result['status'] = 'Skipped';
                $result['reason'] = ($field === 'email' ? 'Email' : 'Phone number')." already exists (lead #{$duplicate->lead_number})";
                $this->skip($result);

                continue;
            }

            try {
                $lead = new Lead();
                $lead->tenant_id = $this->tenantId;
                $lead->lead_type = $row['lead_type'] ?? 'inquiry';
                $lead->lead_source = $row['lead_source'] ?? null;
                $lead->name = $name;
                $lead->phone = $phone;
                $lead->alternate_phone = $row['alternate_phone'] ?? null;
                $lead->email = $email;
                $lead->city = $row['city'] ?? null;
                $lead->state = $row['state'] ?? null;
                $lead->country = $row['country'] ?? null;
                $lead->product = $row['product'] ?? null;
                $lead->service = $row['service'] ?? null;
                $lead->budget = $row['budget'] ?? null;
                $lead->lead_status = $row['lead_status'] ?? 'new';
                $lead->priority = $row['priority'] ?? 'high';
                $lead->status_reason = $row['status_reason'] ?? null;
                $lead->follow_up_date = $this->parseExcelDate($row['follow_up_date'] ?? null);
                $lead->follow_up_time = $this->parseExcelTime($row['follow_up_time'] ?? null);
                $lead->follow_up_note = $row['follow_up_note'] ?? null;
                $lead->requirement = $row['requirement'] ?? null;
                $lead->assigned_to = $this->tenantUserId($row['assigned_to'] ?? null);
                $lead->assigned_by = $this->tenantUserId($row['assigned_by'] ?? null);
                $lead->assigned_at = $row['assigned_at'] ?? null;
                $lead->last_contacted_at = $row['last_contacted_at'] ?? null;
                $lead->last_contacted_by = $this->tenantUserId($row['last_contacted_by'] ?? null);
                $lead->is_converted = 'No';
                $lead->converted_at = null;
                $lead->conversion_value = null;
                $lead->remarks = $row['remarks'] ?? null;
                $lead->internal_note = $row['internal_note'] ?? null;
                $lead->status = $row['status'] ?? 'Active';
                LeadNumberService::saveNew($lead);

                // Recorded only now (not up front) — a row that fails to
                // save must not block a later, genuinely-duplicate row
                // from being correctly reported as a duplicate of the
                // database instead of of this failed row.
                $this->seenEmails[$emailNormalized] = $excelRow;
                $this->seenPhones[$phoneNormalized] = $excelRow;

                $result['status'] = 'Success';
                $this->successCount++;
                $this->results[] = $result;
            } catch (\Throwable $e) {
                $result['reason'] = 'Could not save this row ('.$e->getMessage().')';
                $this->fail($result);
            }
        }
    }

    private function fail(array $result): void
    {
        $result['status'] = 'Failed';
        $this->failedCount++;
        $this->results[] = $result;
    }

    private function skip(array $result): void
    {
        $this->skippedCount++;
        $this->results[] = $result;
    }

    public function summary(): array
    {
        return [
            'total' => $this->totalRows,
            'success' => $this->successCount,
            'skipped' => $this->skippedCount,
            'failed' => $this->failedCount,
        ];
    }

    /** Only the Skipped/Failed rows — what the downloadable report and the on-screen detail table both show. */
    public function problemRows(): array
    {
        return array_values(array_filter($this->results, fn ($r) => $r['status'] !== 'Success'));
    }

    private function parseExcelDate($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            if (is_numeric($value)) {
                return Date::excelToDateTimeObject($value)->format('Y-m-d');
            }

            return Carbon::createFromFormat('d-m-Y', $value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    private function parseExcelTime($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            if (is_numeric($value)) {
                return Carbon::instance(Date::excelToDateTimeObject($value))->format('H:i:s');
            }

            return Carbon::parse($value)->format('H:i:s');
        } catch (\Throwable) {
            return null;
        }
    }

    private function tenantUserId($id): ?int
    {
        if (! $id) {
            return null;
        }

        return User::where('tenant_id', $this->tenantId)->whereKey($id)->value('id');
    }
}
