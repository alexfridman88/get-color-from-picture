<?php
$imageURL = '';
$ex = ['jpg', 'jpeg', 'png', 'gif', 'bpm'];
$error = true;

//upload picture
if (isset($_POST['submit'])) {

    //check errors
    if ($_FILES['image']['error'] == 0) {

        //check upload file from POST
        if (is_uploaded_file($_FILES['image']['tmp_name'])) {

            //get extension of file
            $file_info = pathinfo($_FILES['image']['name']);
            $file_ex = strtolower($file_info['extension']);

            //check extension ['jpg', 'jpeg', 'png', 'gif', 'bpm']
            if (in_array($file_ex, $ex)) {
                $error = false;
                $imageURL = "image.$file_ex";

                //save picture
                move_uploaded_file($_FILES['image']['tmp_name'], $imageURL);
            }
        }
    }

    if ($error) echo '<p>Error uploading this file</p>';
}


function getColors($imageURL){
    /**
     * Get Colors from uploaded image
     * @param - image URL;
     * @return - colors (array);
     * 
     * I get information from https://stackoverflow.com/questions/10290259/detect-main-colors-in-an-image-with-php/10291295
     * and https://www.php.net/manual/en/ref.image.php
     */

    //create image in RAM from any uploaded picture ['jpg', 'jpeg', 'png', 'gif', 'bpm'];
    $img = imagecreatefromstring(file_get_contents($imageURL));

    //get original size from uploaded picture
    $size = getimagesize($imageURL);
    $size_x = $size[0];
    $size_y = $size[1];

    //set new with and height size
    $new_size_x = round($size_x/2);
    $new_size_y = round($size_y/2);

    //create empty picture with new size
    $new_size_img = imagecreatetruecolor($new_size_x,$new_size_y);

    //copy original picture to created picture wiz new size
    imagecopyresized($new_size_img, $img , 0, 0 , 0, 0, $new_size_x, $new_size_y, $size_x, $size_y);

    //delete original picture from RAM
    imagedestroy($img);

    //get color from every second pixel in picture and 
    $colors = [];
    for ($x = 0; $x < $new_size_x; $x+=2) {
        for ($y = $x; $y < $new_size_y; $y+=2) {
            $colors[]=dechex(imagecolorat($new_size_img,$x,$y));
        }
    } 

    //delete copied picture from RAM
    imagedestroy($new_size_img);

    //add '0`s' to colors which have less than 6 symbols
    $colors = array_map(function($colors){
        $color = $colors;
        $zeros = '';
        $max_len = 6;
        $len = strlen($color) ?? '0';
        if($len < $max_len){
            $count = $max_len - $len;
            for($x = 0; $x < $count; $x++){
                $zeros .= "0";
            }
        }
        return $zeros.$color;
    },$colors);

    return $colors;
}


function counter($imageURL){
    /**
     * Counter percent of each colors
     * @param - image Url;
     * @return - percent of each colors (array)
     */

    // set variables 
    $colors = getColors($imageURL);
    $sort_colors = [];
    $color_percent = [];

    //count colors
    foreach ($colors as $color){ 
        if(array_key_exists($color,$sort_colors)){
            $sort_colors[$color] += 1;
        }else{
            $sort_colors[$color] = 1;
        }
    }

  //count percent of each color
  foreach ($sort_colors as $key => $count){
    $color_percent[$key] =   round($count * 100 / array_sum($sort_colors),4) ;
  }

  //sort colors from up to down
  arsort($color_percent);


  return $color_percent;
}
?>

<!-- HTML -->
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <title>Get Colors</title>
    <!-- CSS -->
    <style>
        h1{
            text-align:center;
        }
        form{
            margin: 10px;
            padding: 10px;
            background-color: gray;
            text-align: center;
        }
        .container{
            width:100%;
            text-align: center;
        }
        .left, .right{
            display: inline-block;
            width:45%;
            vertical-align:top;
        }
        .left img{
            width:100%;
            padding-top:5px;
        }
        .colors {
            height: 20px;
            padding: 10px;
            margin: 5px;
            text-align: center;
            text-shadow: #ffffff 0 0 2px;
        }
        span{
            background-color:#ffffff;
            padding:5px;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <h1>Get 10 Strong Colors From You Picture</h1>
    <!-- upload image form -->
    <form action="" method="POST" enctype="multipart/form-data">
        <label for="image">Choose image:</label>
        <input type="file" name="image" id="image">
        <input type="submit" name="submit" value="Upload image">
    </form>

    <!-- check if image was uploaded -->
    <?php if ($imageURL) : ?>
    <div class="container">
        <div class="left">
            <!-- show uploaded image -->
        <img src="<?= $imageURL ?>" alt="">
        </div>
        <div class="right">
            <!-- show colors from image -->
            <?php $colors = counter($imageURL); ?>
            <?php $count = 0;?>
        <?php foreach ($colors as $color => $percent):?>
            <?php ++$count?>
            <div class="colors" style="background-color:#<?=$color?>"><span>#<?=$color?> | <?=$percent?> % </span></div>
            <?php if ($count == 10) break; ?>
        <?php endforeach?>
        </div>
    </div>
    <?php endif ?>
</body>

</html>