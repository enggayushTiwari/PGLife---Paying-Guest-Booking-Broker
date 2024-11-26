<?php
session_start();
require "includes/database_connect.php";

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : NULL;
$city_name = $_GET["city"];

$sql_1 = "SELECT * FROM cities WHERE name = '$city_name'";
$result_1 = mysqli_query($conn, $sql_1);
if (!$result_1) {
    echo "Something went wrong!";
    return;
}
$city = mysqli_fetch_assoc($result_1);
if (!$city) {
    echo "Sorry! We do not have any PG listed in this city.";
    return;
}
$city_id = $city['id'];


$sql_2 = "SELECT * FROM properties WHERE city_id = $city_id";
$result_2 = mysqli_query($conn, $sql_2);
if (!$result_2) {
    echo "Something went wrong!";
    return;
}
$properties = mysqli_fetch_all($result_2, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Best PG's in <?php echo $city_name ?> | PG Life</title>

    <?php
    include "includes/head_links.php";
    ?>
    <link href="css/property_list.css" rel="stylesheet" />
</head>

<body>
    <?php
    include "includes/header.php";
    ?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb py-2">
        <li class="breadcrumb-item">
            <a href="index.php">Home</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">
            <?php echo $city_name; ?>
        </li>
    </ol>
</nav>

<!-- Add download buttons -->
<div class="container mb-3">
    <button class="btn btn-success" onclick="downloadCSV()">Download CSV</button>
    <button class="btn btn-info" onclick="downloadJSON()">Download JSON</button>
    <button class="btn btn-warning" onclick="downloadXML()">Download XML</button>
</div>

<div class="page-container">
        <?php
        foreach ($properties as $property) {
            $property_images = glob("img/properties/" . $property['id'] . "/*");
        ?>
            <div class="property-card property-id-<?= $property['id'] ?> row">
                <div class="image-container col-md-4">
                    <img src="<?= $property_images[0] ?>" />
                </div>
                <div class="content-container col-md-8">
                    <div class="row no-gutters justify-content-between">
                        <?php
                        $total_rating = ($property['rating_clean'] + $property['rating_food'] + $property['rating_safety']) / 3;
                        $total_rating = round($total_rating, 1);
                        ?>
                        <div class="star-container" title="<?= $total_rating ?>">
                            <?php
                            $rating = $total_rating;
                            for ($i = 0; $i < 5; $i++) {
                                if ($rating >= $i + 0.8) {
                            ?>
                                    <i class="fas fa-star"></i>
                                <?php
                                } elseif ($rating >= $i + 0.3) {
                                ?>
                                    <i class="fas fa-star-half-alt"></i>
                                <?php
                                } else {
                                ?>
                                    <i class="far fa-star"></i>
                            <?php
                                }
                            }
                            ?>
                        </div>
                    </div>

                    <div class="detail-container">
                        <div class="property-name"><?= $property['name'] ?></div>
                        <div class="property-address"><?= $property['address'] ?></div>
                        <div class="property-gender">
                            <?php
                            if ($property['gender'] == "male") {
                            ?>
                                <img src="img/male.png" />
                            <?php
                            } elseif ($property['gender'] == "female") {
                            ?>
                                <img src="img/female.png" />
                            <?php
                            } else {
                            ?>
                                <img src="img/unisex.png" />
                            <?php
                            }
                            ?>
                        </div>
                    </div>
                    <div class="row no-gutters">
                        <div class="rent-container col-6">
                            <div class="rent">₹ <?= number_format($property['rent']) ?>/-</div>
                            <div class="rent-unit">per month</div>
                        </div>
                        <div class="button-container col-6">
                            <a href="property_detail.php?property_id=<?= $property['id'] ?>" class="btn btn-primary">View</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
        ?>
    </div>

    <?php
    include "includes/signup_modal.php";
    include "includes/login_modal.php";
    include "includes/footer.php";
    ?>
<script>
    // Store properties data in JavaScript
    const propertiesData = <?php echo json_encode($properties); ?>;

    function downloadCSV() {
        let csvContent = "data:text/csv;charset=utf-8,ID,Name,Address,Gender,Rent,Rating\n";
        propertiesData.forEach(property => {
            const rating = ((property.rating_clean + property.rating_food + property.rating_safety) / 3).toFixed(1);
            csvContent += `${property.id},${property.name},${property.address},${property.gender},${property.rent},${rating}\n`;
        });
        
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "properties_<?php echo $city_name; ?>.csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    function downloadJSON() {
        const dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(propertiesData, null, 2));
        const link = document.createElement("a");
        link.setAttribute("href", dataStr);
        link.setAttribute("download", "properties_<?php echo $city_name; ?>.json");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    function downloadXML() {
        let xmlContent = '<?xml version="1.0" encoding="UTF-8"?>\n<properties>\n';
        propertiesData.forEach(property => {
            const rating = ((property.rating_clean + property.rating_food + property.rating_safety) / 3).toFixed(1);
            xmlContent += `  <property>\n`;
            xmlContent += `    <id>${property.id}</id>\n`;
            xmlContent += `    <name>${property.name}</name>\n`;
            xmlContent += `    <address>${property.address}</address>\n`;
            xmlContent += `    <gender>${property.gender}</gender>\n`;
            xmlContent += `    <rent>${property.rent}</rent>\n`;
            xmlContent += `    <rating>${rating}</rating>\n`;
            xmlContent += `  </property>\n`;
        });
        xmlContent += '</properties>';
        
        const dataStr = "data:text/xml;charset=utf-8," + encodeURIComponent(xmlContent);
        const link = document.createElement("a");
        link.setAttribute("href", dataStr);
        link.setAttribute("download", "properties_<?php echo $city_name; ?>.xml");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
</script>
</body>
</html>