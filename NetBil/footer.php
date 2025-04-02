<footer class="main-footer">
    <div class="float-right d-none d-sm-inline">
        v1.0.0
    </div>
    <strong>Copyright &copy; 2025 <a href="#">BrenNet</a>.</strong> All rights reserved.
</footer>

<!-- jQuery -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<!-- Bootstrap 5 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.1/dist/js/adminlte.min.js"></script>

<script>


// Handle sidebar active state
$(document).ready(function() {
    $('.nav-link').removeClass('active');
    $('.nav-link[href="'+window.location.pathname.split('/').pop()+'"]').addClass('active');
});
</script>
</body>
</html>