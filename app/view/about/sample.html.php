<?php
$bodyClass = 'about';
include __DIR__ . '/../prologue.html.php';
include __DIR__ . '/../header.html.php';
?>
<?php if (\NODE_ID == \GOTEO_NODE) : ?>
    <div id="sub-header">
        <div>
            <h2><?php echo $this['description']; ?></h2>
        </div>
    </div>
<?php endif; ?>

<?php if(isset($_SESSION['messages'])) { include __DIR__ . '/../header/message.html.php'; } ?>

    <div id="main">

        <div class="widget">
            <h3 class="title"><?php echo $this['name']; ?></h3>
            <?php echo $this['content']; ?>
        </div>

    </div>

<?php include __DIR__ . '/../footer.html.php' ?>

<?php include __DIR__ . '/../epilogue.html.php' ?>
