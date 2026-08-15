<footer class="footer">
  <div class="d-sm-flex justify-content-center justify-content-sm-between">
    <span class="text-muted text-center text-sm-left d-block d-sm-inline-block">&copy; {{ date('Y') }} {{ config('app.name', 'CRM') }}. All rights reserved.</span>
  </div>
</footer>

<style>
  html, body {
    height: 100%;
  }

  .container-scroller {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
  }

  .page-body-wrapper {
    flex: 1;
    display: flex;
  }

  .content-wrapper {
    flex: 1;
    display: flex;
    flex-direction: column;
  }

  .content-wrapper > footer {
    margin-top: auto;
  }
</style>

<script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>
<script src="{{ asset('js/crm-pagination.js') }}"></script>
