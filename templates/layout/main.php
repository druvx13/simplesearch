<?php
// Main layout: header + content + footer
// $content is the rendered template content
// $templatesPath is the templates directory path
$_headerFile = $this->getTemplatesPath() . '/layout/header.php';
$_footerFile = $this->getTemplatesPath() . '/layout/footer.php';
?>
<?php include $_headerFile; ?>
<?php echo $content; ?>
<?php include $_footerFile; ?>
