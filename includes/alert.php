<?php
    if (isset($_GET['msg'])){
        echo "<div class='alert alert-success'>";
        echo "<span class='closebtn' onclick=\"this.parentElement.style.display='none';\">&times;</span>";
        echo $_GET['msg'];
        echo "</div>";
    }
?>
<?php
    if (isset($_GET['error'])){
        echo "<div class='alert alert-error'>";
        echo "<span class='closebtn' onclick=\"this.parentElement.style.display='none';\">&times;</span>";
        echo $_GET['error'];
        echo "</div>";
    }
?>

<?php
    echo "
        <script>
        var alertElement = document.querySelector('.alert');
        var timeout = 3000;

        function hideAlert() {
            if (alertElement) {
                alertElement.style.display = 'none';
            }
        }
        
        setTimeout(hideAlert, timeout);
        </script>
    "
?>
