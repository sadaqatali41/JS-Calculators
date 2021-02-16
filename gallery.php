<?php
include 'inc/resize-class.php';
?>
<!DOCTYPE html>
<html>

<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<header class="main-header">
    
</header>
<div class="content-wrapper">
    <?php
if (filter_input(INPUT_GET, "act") !== null) {
	$act = filter_input(INPUT_GET, "act", FILTER_SANITIZE_STRING);
} else {
	$act = "";
}
switch ($act) {
case "add":

	$prod_model = '';

	$file = "";
	$file_base_name = "";
	$trgtIMG = "img/gallery";
	$trgtImgThumb = "img/gallery/thumb";

	if (filter_has_var(INPUT_POST, 'quality')) {
		$prod_model = (trim(filter_input(INPUT_POST, "prod_model", FILTER_VALIDATE_INT)));

		if (isset($_FILES['file']['tmp_name']) && !empty($_FILES['file']['tmp_name'])) {
			$file_base_name = basename($_FILES['file']['name']);
			$file = $_FILES['file']['tmp_name'];
		}

		if (empty($prod_model)) {$error[0] = "Product model is required.";}
		if (empty($file)) {$error[2] = "Image is required.";}

		if (empty($error)) {
			$allowed_types = array('jpg', 'jpeg', 'png');
			$extension = pathinfo($file_base_name, PATHINFO_EXTENSION);

			if (!in_array($extension, $allowed_types)) {
				$error[3] = 'File type not allowed.';
			}
		}

		if (empty($error)) {
			if (filesize($_FILES['file']['tmp_name']) > 1000000) {
				$error[4] = 'Maximum file size allowed is 1MB.';
			}
		}

		if (empty($error)) {
			$fileBrackArray = explode(".", strtolower($file_base_name));
			$file_ext = end($fileBrackArray);
			$newFileName = md5(rand() * time()) . ".$file_ext";

			list($img_width, $img_height, $img_type, $img_attr) = getimagesize($file);

			if (($img_width < 1600) || ($img_height < 900)) {
				$error[5] = "Image should have minimum width of 1600px and minimum height of 900px.";
			}
		}

		if (empty($error)) {
			if (move_uploaded_file($file, "$trgtIMG/$newFileName")) {
				$resizeObj = new resize("$trgtIMG/$newFileName");
				$resizeObj->resizeImage(1600, 900, "crop");
				$resizeObj->saveImage("$trgtIMG/$newFileName", 100);

				#thumb
				$resizeObj->resizeImage(500, 300, "crop");
				$resizeObj->saveImage("$trgtImgThumb/$newFileName", 100);

				$stmt = $conn->prepare("INSERT INTO table_name (prod_model, image) VALUES (?,?)");
				$stmt->bind_param("is", $prod_model, $newFileName);
				$stmt->execute();
				print '<div class="alert alert-success alert-dismissable"><button class="close" type="button" data-dismiss="alert" aria-hidden="true">×</button>Success, product gallery is added successfully.</div>';

				$prod_model = "";
				$stmt->close();
			}
		}
	}

	?>
    <?php
break;
case "edit":

	if (filter_has_var(INPUT_GET, 'id')) {
		$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

		$title = "";
		$prod_model = "";
		$event_pic = "";
		$file = "";
		$file_base_name = "";
		$trgtIMG = "img/gallery";
		$trgtImgThumb = "img/gallery/thumb";

		$stmt = $conn->prepare("SELECT pg.*, pm.title FROM table_name pg LEFT JOIN table_name1 pm ON pg.prod_model=pm.id WHERE pg.id=?");
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$resSm = $stmt->get_result();
		if ($resSm->num_rows > 0) {
			$rowSm = $resSm->fetch_assoc();
			$title = $rowSm['title'];
			$prod_model = $rowSm['prod_model'];
			$event_pic = $rowSm['image'];
		} else {
			header('location: gallery.php');
		}

		#form submission
		if (filter_has_var(INPUT_POST, 'editQuality')) {

			$prod_model = (trim(filter_input(INPUT_POST, "prod_model", FILTER_SANITIZE_STRING)));

			if (isset($_FILES['file']['tmp_name']) && !empty($_FILES['file']['tmp_name'])) {
				$file_base_name = basename($_FILES['file']['name']);
				$file = $_FILES['file']['tmp_name'];
			}

			if (empty($prod_model)) {$error[0] = "Product model is required.";}

			if (empty($error)) {

				if (isset($file) && !empty($file)) {
					$allowed_types = array('jpg', 'jpeg', 'png');
					$extension = pathinfo($file_base_name, PATHINFO_EXTENSION);

					if (!in_array($extension, $allowed_types)) {
						$error[3] = 'File type not allowed.';
					}
					if (empty($error)) {
						if (filesize($_FILES['file']['tmp_name']) > 1000000) {
							$error[4] = 'Maximum file size allowed is 1MB.';
						}
					}
					if (empty($error)) {
						$fileBrackArray = explode(".", strtolower($file_base_name));
						$file_ext = end($fileBrackArray);
						$newFileName = md5(rand() * time()) . ".$file_ext";
						list($img_width, $img_height, $img_type, $img_attr) = getimagesize($file);
						if (($img_width < 1600) || ($img_height < 900)) {
							$error[5] = "Image should have minimum width of 1600px height of 900px.";
						}
					}
					if (empty($error)) {
						if (move_uploaded_file($file, "$trgtIMG/$newFileName")) {
							$resizeObj = new resize("$trgtIMG/$newFileName");
							$resizeObj->resizeImage(1600, 900, "crop");
							$resizeObj->saveImage("$trgtIMG/$newFileName", 100);

							#thumb
							$resizeObj->resizeImage(500, 300, "crop");
							$resizeObj->saveImage("$trgtImgThumb/$newFileName", 100);

							if (isset($event_pic) && !empty($event_pic)) {
								@unlink("img/gallery/" . $event_pic);
								@unlink("img/gallery/thumb/" . $event_pic);
							}
							$event_pic = $newFileName;
							$stmt = $conn->prepare("UPDATE table_name SET prod_model=?, image=? WHERE id=?");
							$stmt->bind_param("isi", $prod_model, $newFileName, $id);
							if ($stmt->execute()) {
								print '<div class="alert alert-success alert-dismissable">
                                    <button class="close" type="button" data-dismiss="alert" aria-hidden="true">×</button>
                                    Success, xyz is updated successfully.
                                </div>';
							}
						}
					}
				} else {
					$stmt = $conn->prepare("UPDATE table_name SET prod_model=? WHERE id=?");
					$stmt->bind_param("ii", $prod_model, $id);
					if ($stmt->execute()) {
						print '<div class="alert alert-success alert-dismissable">
                                    <button class="close" type="button" data-dismiss="alert" aria-hidden="true">×</button>
                                    Success, xyz is updated successfully.
                                </div>';
					}
				}
			}
		}
	}

	?>
    <?php
break;
default:
	?>
    
<?php
break;
}
?>
    </div>
    <!-- /.content-wrapper -->
</div>
</body>
</html>
