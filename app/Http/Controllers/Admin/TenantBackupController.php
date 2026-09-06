<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

/**
 * Surfaces the SQL dumps TenantDatabaseBackupService writes to
 * storage/app/tenant-backups before TenantController::destroy() drops a
 * company's database — until this existed they were on-disk only, reachable
 * solely via server SSH access.
 */
class TenantBackupController extends Controller
{
    private function directory(): string
    {
        return storage_path('app/tenant-backups');
    }

    public function index()
    {
        File::ensureDirectoryExists($this->directory());

        $backups = collect(File::files($this->directory()))
            ->filter(fn ($file) => $file->getExtension() === 'sql')
            ->map(fn ($file) => [
                'name' => $file->getFilename(),
                'size' => $file->getSize(),
                'modified_at' => $file->getMTime(),
            ])
            ->sortByDesc('modified_at')
            ->values();

        return view('platform.backups', compact('backups'));
    }

    public function download(string $filename)
    {
        $path = $this->resolve($filename);

        return response()->download($path);
    }

    public function destroy(string $filename)
    {
        File::delete($this->resolve($filename));

        return back()->with('success', 'Backup file deleted.');
    }

    /**
     * basename() alone strips directory traversal from the segment, but the
     * result still has to land inside the backups directory and actually be
     * one of our own .sql dumps before it's trusted.
     */
    private function resolve(string $filename): string
    {
        $safeName = basename($filename);
        $path = $this->directory().DIRECTORY_SEPARATOR.$safeName;

        abort_unless(
            str_ends_with($safeName, '.sql') && File::exists($path) && dirname($path) === $this->directory(),
            404
        );

        return $path;
    }
}
