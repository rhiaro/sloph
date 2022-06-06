<?
include 'top.php';
include 'header.php';
?>
<main>
<?
include 'nav.php';
foreach($includes as $include){
    include $include;
}
include 'nav.php';
?>
</main>
<?
include 'end.php';
?>