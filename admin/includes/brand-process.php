<?php
include 'config.php'; // Database Connection

// Add new brands ================================================================================
if (isset($_POST['brandAdd'])) {
    // Get the form data
    $brandName = $_POST['brandName'];
    $brandLogo = $_FILES['brandLogo']['name'];
    $brandLogoTmp = $_FILES['brandLogo']['tmp_name'];

    // Set the target directory for uploading the brand logo
    $targetDirectory = "../assets/images/uploads/brands/";

    // Generate a unique filename for the brand logo
    $brandLogoFileName = uniqid() . '_' . $brandLogo;

    // Set the target path for the brand logo
    $targetFilePath = $targetDirectory . $brandLogoFileName;

    // Move the uploaded brand logo to the target directory
    if (move_uploaded_file($brandLogoTmp, $targetFilePath)) {

        $sql = "INSERT INTO brands (brand_name, brand_logo, creation_date) 
                VALUES ('$brandName', '$brandLogoFileName', NOW())";

        if ($conn->query($sql) === TRUE) {
            $msg = "Brand added successfully.";
            header('Location: ../brands.php?msg=' . urlencode($msg));
            exit();
        } else {
            $error = "Error adding brand: " . $conn->error;
            header('Location: ../brands.php?error=' . urlencode($error));
            exit();
        }
    } else {
        $error = "Error uploading brand logo.";
        header('Location: ../brands.php?error=' . urlencode($error));
        exit();
    }
} elseif (isset($_GET['updateBrand'])) { // Edit brands ================================================================================
    $brandId = $_GET['brandId'];
    $brandName = $_GET['brandName'];
    $brandStatus = $_GET['brandStatus'];

    //Check if a new logo was uploded
    if ($_FILES['brandLogo']['name'] > 0) {
        
        $brandLogo = $_FILES['brandLogo']['name'];

        // Upload the brand logo to a desired directory
        $brandLogoDestination = '../assets/images/uploads/logo/' . basename($brandLogo);
        $sourceFile = $_FILES['brandLogo']['tmp_name'];

        if (move_uploaded_file($sourceFile, $brandLogoDestination)) {
            // Update the brand data in the SQL table with the new logo
            $sql = "UPDATE brands SET brand_name = '$brandName', brand_logo = '$brandLogo',  brand_status = '$brandStatus', update_date = NOW()
                        WHERE brand_id = $brandId";
        } else {
            $error = "Error uploading brand logo.";
            header('Location: ../brands.php?brandId=' . $brandId . '&error=' . $error);
            exit();
        }
    } else {
        // Update the brand data in the SQL table without changing the logo
        $sql = "UPDATE brands SET brand_name = '$brandName', brand_status = '$brandStatus' , update_date = NOW() WHERE brand_id = $brandId";
    }

    if ($conn->query($sql) === TRUE) {
        $msg = "Brand updated successfully.";
        header('Location: ../brands.php?msg=' . $msg);
        exit();
    } else {
        $error = "Error updating brand: " . $conn->error;
        header('Location: ../brands.php?brandId=' . $brandId . '&error=' . $error);
        exit();
    }

} elseif (isset($_GET['brand_id'])) { // Delete Brand ================================================================================
    $brandId = $_GET['brand_id'];

    // Perform the delete operation
    $sql = "DELETE FROM brands WHERE brand_id = $brandId";
    if ($conn->query($sql) === TRUE) {
        $msg = "Brand deleted successfully.";
        header("Location: ../brands.php?msg=$msg");
        exit();
    } else {
        $error = "Error deleting Brand: " . $conn->error;
        header("Location: ../brands.php?msg=$error");
    }
}

?>