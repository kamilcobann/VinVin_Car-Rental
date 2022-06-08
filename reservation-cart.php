<?php
session_start();
require_once "connect.php";

if (!isset($_SESSION["loggedin"])) {
    header("Location:main.php");
}elseif(isset($_GET["logout"])){
  session_destroy();
  header('Location:main.php');
}elseif(!isset($_GET["carInCart"])){
    echo "An error occured";
    header("Location:main.php");
}
$rentedCarID = $_GET['carInCart'];

$sql = "UPDATE cars SET statement='0' WHERE carID='$rentedCarID'";
$result = $conn->query($sql);


$rent_day = $_SESSION["rentDay"];
$rent_time = $_SESSION["rentTime"];
$return_day = $_SESSION["returnDay"]; 
$return_time = $_SESSION["returnTime"];
$branchID = $_SESSION["town"];
$classID = $_SESSION["segment"]; 

$rentDuration="";

function  hourToMS($str){
    $time= explode(":",$str);
    $hour = $time[0]*60*60*1000;
    $minute = $time[1]*60*1000;
    $result = $hour+$minute;
    
    return $result;
}

function dateToMS($str1,$str2){
    $time= explode("-",$str1);
    $year = $time[0]*365*86400000;
    $month = $time[1]*30*86400000;
    $day = $time[2]*86400000;
    $ms = hourToMS($str2);
    $result = $year + $month + $day + $ms;
    return $result;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="style.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">

    <title>VınVın | Car Selection</title>
  </head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-success p-2 text-white fixed-top">
      <div class="container-fluid">
          <a class="navbar-brand icon" href="main.php">
              <img src="images/car.png" alt="" width="60" height="60" class="d-inline-block align-text-top">
            </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
          <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <li class="nav-item mx-3">
              <a class="nav-link text-white" aria-current="page" href="main.php">VınVın</a>
            </li>
            <li class="nav-item mx-3">
              <a class="nav-link text-white" href="cars.php">Cars</a>
            </li>
            <li class="nav-item mx-3">
                <a href="contact.php" class="nav-link text-white">Contact</a>
            </li>
            
          </ul>
          <?php if (
              isset($_SESSION["loggedin"]) &&
              $_SESSION["loggedin"] === true
          ) { ?>
          <ul class="navbar-nav ms-auto mb-2 mb-lg-0 account ">
              <li class="nav-item">
              <a href="main.php?logout" class="mt-2 me-2 btn text-white">Sign out</a>
              </li>
              <li class="nav-item">
                  <p class="mt-3 me-3">User</p>
              </li>
              <li>
              <img
              width="50px"
              height="50px"
                  class="admin-icon mt-2 rounded-circle "
                  src="https://images.unsplash.com/photo-1633332755192-727a05c4013d?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxzZWFyY2h8MXx8dXNlcnxlbnwwfHwwfHw%3D&w=1000&q=80" alt="">
              </li>
              
          </ul>
          <?php } else { ?>
          <ul class="navbar-nav ms-auto mb-2 mb-lg-0 account ">
              <li class="nav-item">
                  <div class="btn text-white" onclick="popUpLogin()">Login</div>
                  <div class="dropdown-menu action-form" id="loginWindow">
                    <div class="container text-center">
                      <p class="mt-3">Login as Admin</p>
                      <input type="button" value="Admin" class="btn btn-outline-success" onclick="relocate_Admin()">
                    </div>
                    <form method="post">
              
                      <div class="text-center mt-3"><b>or</b></div>
                      <div class="form-group my-3 mx-3">
                        <input type="email" class="form-control" placeholder="Email" required="required" name="email" id="email"
                        <?php echo !empty($email_err)
                            ? "is-invalid"
                            : ""; ?>value="<?php echo $email; ?>" >
                        <span><?php echo $email_err; ?></span>

                      </div>
                      <div class="form-group mb-3 mx-3">
                        <input type="password" class="form-control" placeholder="Password" required="required" name="password" id="password" <?php echo !empty(
                            $password_err
                        )
                            ? "is-invalid"
                            : ""; ?>value="<?php echo $password; ?>" >
                        <span><?php echo $password_err; ?></span>
                      </div>
                      <div class="mb-3 ms-3 form-check">
                          <input type="checkbox" class="form-check-input" id="exampleCheck1">
                          <label class="form-check-label" for="exampleCheck1">Keep Signed In</label>
                        </div>
                      <div class="form-group float-start mb-3 mx-3">
                        <a href="cars.phpmain.php" class="btn">Forgot Password?</a>
                      </div>
                      <div class="form-group float-end mb-3 me-3">
                        <input type="submit" class="btn btn-outline-warning btn-success  btn-block" value="Login">
                      </div>
                    </form>
                  </div>
                </li>
              <li class="nav-item">
                  <div class="btn text-white" onclick="redirectRegister()">Sign Up</div>
              </li>
          </ul>
          <?php } ?>
        </div>
      </div>
</nav>

<div class="container">
  <div class="row mt-3">
    <div class="col-sm-8 offset-2">
        <div class="row">
        <?php
            $sql = "SELECT * FROM cars AS c
            INNER JOIN class as cl ON c.classID = cl.classID
            INNER JOIN branches as b ON c.branchID = b.branchID
            WHERE c.carID='$rentedCarID'";
            $result = $conn->query($sql);
            if($result->num_rows > 0){
                while($row = $result->fetch_assoc()){
         ?>
            <div class="card mb-3">
              <div class="row g-0">
                <div class="col-md-4">
                  <img src="<?=$row["imageURL"]?>" width="300px" height="200px" class="img-fluid" alt="...">
                </div>
                <div class="col-md-8">
                  <div class="card-body">
                    <h5 class="card-title"><?=$row["carbrand"]." ".$row["carmodel"]?></h5>
                    <p class="card-text">
                        Segment:<?= $row["className"]?>
                        <br>
                        Now In:<?=$row["branchname"]?>
                        <br>
                        Plate:<?=$row["plate"]?>
                        <br>
                        Price:<?=$row["price"]?>/₺per day
                    </p>
                  </div>
                </div>
              </div>
            </div>
         

            <div class="rent col border rounded-1 h-100">
                          <div class="rent-Day my-2">Rent Day: <?= $rent_day ?></div>
                          <div class="rent-Time mb-2">Rent Time : <?= $rent_time?></div>
                          
                          
        </div> 
                 <div class="rent col border rounded-1 h-100">
                          
                          
                          <div class="rent-Day my-2">Return Day : <?= $return_day ?></div>
                          <div class="rent-Time mb-2">Return Time : <?= $return_time?></div>
        </div>
        <div class="col mt-3">
            <?php 
               $rentDuration = dateToMS($return_day,$return_time)-dateToMS($rent_day,$rent_time);
               
               if($rentDuration>0){
                   $cost = ($rentDuration/86400000)*$row["price"];
                   $_SESSION["cost"] =$cost;
                   ?>
                    <h4 class="mt-1 float-start">Cost : <?=$cost?>₺</h4>
                    
                    <a class="btn btn-outline-success ms-2" href="reservation-payment.php?rentedCAR=<?=$row["carID"]?>">Go To Payment</a>
                   <?php
               }else{
                   echo "RENT OR RETURN DAY/TIME MAY BE WRONG TRY AGAIN!";
                   header("Location:main.php");
               }
            }
        }
            ?>
        </div>
  </div>
  </div>
</div>
<div class="container fixed-bottom">
      <footer class="d-flex flex-wrap justify-content-between align-items-center py-3 my-4 border-top">
        <p class="col-md-4 mb-0 text-muted">© 2021 Company, Inc</p>
        <a href="main.php">
        <img src="images/car.png" width="40px" height="40px" alt="">
      </a>
        <ul class="nav col-md-4 justify-content-end">
          <li class="nav-item"><a href="main.php" class="nav-link px-2 text-muted">Home</a></li>
          <li class="nav-item"><a href="cars.php" class="nav-link px-2 text-muted">Cars</a></li>
          <li class="nav-item"><a href="contact.php" class="nav-link px-2 text-muted">FAQs</a></li>
          <li class="nav-item"><a href="contact.php" class="nav-link px-2 text-muted">About</a></li>
        </ul>
      </footer>
    </div>
  </body>

  </html>