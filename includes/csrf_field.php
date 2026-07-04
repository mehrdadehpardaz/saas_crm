<?php
// includes/csrf_field.php
echo '<input type="hidden" name="_csrf" value="' . crm_csrf_token() . '">';
?>