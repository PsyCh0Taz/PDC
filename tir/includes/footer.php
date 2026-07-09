</div><!-- /.container-fluid -->

<footer class="footer mt-5 py-3 bg-dark text-light">
    <div class="container text-center">
        <small><?php echo h(APP_NAME); ?> &copy; <?php echo date('Y'); ?></small>
    </div>
</footer>

<!-- jQuery + Bootstrap 4 JS -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
<!-- JS personnalisé -->
<script src="<?php echo APP_URL; ?>/assets/js/app.js"></script>
<?php if (isset($extra_scripts)) echo $extra_scripts; ?>
</body>
</html>
