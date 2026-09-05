<?php
// include language configuration file based on selected language
$lang = "en";
if (isset($_GET['lang'])) {
   $lang = $_GET['lang'];
    $_SESSION['lang'] = $lang;
}
if( isset( $_SESSION['lang'] ) ) {
    $lang = $_SESSION['lang'];
}else {
    $lang = "en";
}
require_once ("./assets/lang/" . $lang . ".php");

?>
<!DOCTYPE html>
<html lang="<?php echo $lang ?>">
<?php if (empty($crmForceLightTheme)) : ?>
<script>
/* Earliest possible theme boot — prevents white flash before dark CSS paints */
(function () {
    try {
        if (localStorage.getItem('crm_theme') === 'dark') {
            document.documentElement.classList.add('crm-dark', 'crm-dark-preload');
            document.documentElement.style.colorScheme = 'dark';
            document.documentElement.style.backgroundColor = '#0b0f1e';
        }
    } catch (e) {}
})();
</script>
<?php else : ?>
<script>
/* Auth / light-only pages — never inherit app dark mode */
(function () {
    try {
        document.documentElement.classList.remove('crm-dark', 'crm-dark-preload');
        document.documentElement.style.colorScheme = 'light';
        document.documentElement.style.backgroundColor = '';
    } catch (e) {}
})();
</script>
<?php endif; ?>