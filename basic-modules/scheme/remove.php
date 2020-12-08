<?php
include("../../functions.php");
if(isset($_POST['schemeId'])){
    $sid = $_POST['schemeId']; 
    // remove Products
    if(removeProductsBySid($sid)){
        echo "Products has been removed";
        if(removeTieresBySid($sid)){
            echo "Tieres has been removed";
            if(removeSchemeById($sid)){
                echo "A scheme $sid has been remove Successfully";
            }else{  
                echo "Remove a scheme has been failed";
            }
        }else{
            echo "Remove Tieres has been failed";
        }
    }else{
        echo "Remove Products has been failed";
    }
}