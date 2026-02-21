<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Company Details | CRM</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />



    <style>
        :root {
            --bg: #f5f7fb;
            --card: #ffffff;
            --border: #e6e9f0;
            --text: #1f2937;
            --muted: #6b7280;
            --primary: #2563eb;
        }

        body {
            background: var(--bg);
            font-family: "Inter", system-ui, sans-serif;
        }

        .company-layout {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 24px;
        }

        @media (max-width: 992px) {
            .company-layout {
                grid-template-columns: 1fr;
            }
        }

        .panel {
            background: var(--card);
            border-radius: 14px;
            border: 1px solid var(--border);
            padding: 22px;
        }

        .company-panel {
            text-align: center;
        }

        .company-logo-wrapper {
            width: 120px;
            height: 120px;
            margin: 0 auto 16px;
            border-radius: 16px;
            overflow: hidden;
            background: #eef2ff;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .company-logo-img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .company-logo-placeholder {
            font-size: 36px;
            font-weight: 700;
            color: var(--primary);
        }

        .section {
            margin-bottom: 24px;
        }

        .section-title {
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 16px;
            border-bottom: 1px solid var(--border);
            padding-bottom: 8px;
        }

        label {
            font-size: 12px;
            font-weight: 600;
            color: var(--muted);
        }
    </style>
</head>

<body>

    <div class="container-scroller">
        @include('admin.include.header')

        <div class="container-fluid page-body-wrapper">
            @include('admin.include.sidebar')

            <div class="content-wrapper">

                <h3 class="mb-4">Edit Company Details</h3>

                <form id="edit_com"
                    method="POST"
                    enctype="multipart/form-data">

                    @csrf

                    <div class="company-layout">

                        <!-- LEFT PANEL -->
                        <div class="panel company-panel">

                            <div class="company-logo-wrapper" id="logoUploadArea" title="Click to upload logo">

                                @if(!empty($company->company_logo))
                                <img
                                    src="{{ asset($company->company_logo) }}"
                                    id="companyLogoPreview"
                                    class="company-logo-img"
                                    alt="Company Logo">
                                @else
                                <div
                                    class="company-logo-placeholder"
                                    id="companyLogoPreview">
                                    {{ strtoupper(substr($company->company_name, 0, 1)) }}
                                </div>
                                @endif

                            </div>

                            <!-- Hidden File Input -->
                            <input
                                type="file"
                                name="company_logo"
                                id="companyLogoInput"
                                accept="image/*"
                                style="display:none;">

                            <div class="form-group mt-3">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <option value="Active" {{ $company->status == 'Active' ? 'selected' : '' }}>Active</option>
                                    <option value="Inactive" {{ $company->status == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                        </div>

                        <!-- RIGHT PANEL -->
                        <div>

                            <!-- BASIC INFO -->
                            <div class="panel section">
                                <div class="section-title">Basic Information</div>
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label>Company Name</label>
                                        <input type="text" name="company_name" class="form-control"
                                            value="{{ $company->company_name }}">
                                    </div>

                                    <div class="col-md-6 form-group">
                                        <label>Email</label>
                                        <input type="email" name="email" class="form-control"
                                            value="{{ $company->email }}">
                                    </div>

                                    <div class="col-md-6 form-group">
                                        <label>Phone</label>
                                        <input type="text" name="phone" class="form-control"
                                            value="{{ $company->phone }}">
                                    </div>
                                </div>
                            </div>

                            <!-- ADDRESS -->
                            <div class="panel section">
                                <div class="section-title">Address</div>

                                <div class="row">
                                    <div class="col-md-12 form-group">
                                        <label>Address</label>
                                        <textarea name="address" class="form-control">{{ $company->address }}</textarea>
                                    </div>

                                    <div class="col-md-4 form-group">
                                        <label>City</label>
                                        <input type="text" name="city" class="form-control"
                                            value="{{ $company->city }}">
                                    </div>

                                    <div class="col-md-4 form-group">
                                        <label>State</label>
                                        <input type="text" name="state" class="form-control"
                                            value="{{ $company->state }}">
                                    </div>

                                    <div class="col-md-4 form-group">
                                        <label>Country</label>
                                        <input type="text" name="country" class="form-control"
                                            value="{{ $company->country }}">
                                    </div>

                                    <div class="col-md-4 form-group">
                                        <label>Pincode</label>
                                        <input type="text" name="pincode" class="form-control"
                                            value="{{ $company->pincode }}">
                                    </div>
                                </div>
                            </div>

                            <!-- TAX -->
                            <div class="panel section">
                                <div class="section-title">Tax Information</div>

                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label>GST Number</label>
                                        <input type="text" name="gst_number" class="form-control"
                                            value="{{ $company->gst_number }}">
                                    </div>

                                    <div class="col-md-6 form-group">
                                        <label>PAN Number</label>
                                        <input type="text" name="pan_number" class="form-control"
                                            value="{{ $company->pan_number }}">
                                    </div>
                                </div>
                            </div>

                            <!-- BANK -->
                            <div class="panel section">
                                <div class="section-title">Bank Details</div>

                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label>Bank Name</label>
                                        <input type="text" name="bank_name" class="form-control"
                                            value="{{ $company->bank_name }}">
                                    </div>

                                    <div class="col-md-6 form-group">
                                        <label>Account Name</label>
                                        <input type="text" name="account_name" class="form-control"
                                            value="{{ $company->account_name }}">
                                    </div>

                                    <div class="col-md-6 form-group">
                                        <label>Account Number</label>
                                        <input type="text" name="account_number" class="form-control"
                                            value="{{ $company->account_number }}">
                                    </div>

                                    <div class="col-md-6 form-group">
                                        <label>IFSC Code</label>
                                        <input type="text" name="ifsc_code" class="form-control"
                                            value="{{ $company->ifsc_code }}">
                                    </div>
                                </div>
                            </div>

                            <!-- ACTION -->
                            <div class="text-right mb-4">
                                <a href="" class="btn btn-light">Cancel</a>
                                <button type="submit" class="btn btn-primary">
                                    Update Company
                                </button>
                            </div>

                        </div>
                    </div>
                </form>

                @include('admin.include.footer')
            </div>
        </div>
    </div>


    <!-- jQuery (ONLY ONCE) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap JS (REQUIRED for modal) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Toastr -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>



    <script>
        // Click on logo opens file chooser
        document.getElementById('logoUploadArea').addEventListener('click', function() {
            document.getElementById('companyLogoInput').click();
        });

        // Preview selected image
        document.getElementById('companyLogoInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function(event) {

                const preview = document.getElementById('companyLogoPreview');

                // If placeholder div exists, replace with img
                if (preview.tagName !== 'IMG') {
                    const img = document.createElement('img');
                    img.id = 'companyLogoPreview';
                    img.className = 'company-logo-img';
                    img.src = event.target.result;

                    preview.parentNode.replaceChild(img, preview);
                } else {
                    preview.src = event.target.result;
                }
            };

            reader.readAsDataURL(file);
        });


        $(document).on('submit', '#edit_com', function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            $.ajax({
                url: '{{route("admin.edit_com_details")}}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.status) {
                        toastr.success(response.message);
                        window.location.href = "{{route('admin.company_details')}}";
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(err) {
                    toastr.error(err.message);
                }
            })
        })
    </script>

</body>

</html>