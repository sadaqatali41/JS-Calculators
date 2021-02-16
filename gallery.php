<?php
include 'inc/config.php';
include 'inc/func.php';
include 'inc/pagination.php';
include 'inc/resize-class.php';
sec_session_start();
if (login_check($conn) === FALSE) {
	header("location: login.php");
} else {
	$userId = $_SESSION['user_id'];
	$user = getLoginData($userId, $conn);
}
?>
<!DOCTYPE html>
<html>
<?php include 'inc/head.php';?>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<header class="main-header">
    <?php include 'inc/header.php';?>
</header>
<?php include 'inc/nav_sidebar.php';?>
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

				$stmt = $conn->prepare("INSERT INTO prod_gallery (prod_model, image) VALUES (?,?)");
				$stmt->bind_param("is", $prod_model, $newFileName);
				$stmt->execute();
				print '<div class="alert alert-success alert-dismissable"><button class="close" type="button" data-dismiss="alert" aria-hidden="true">×</button>Success, product gallery is added successfully.</div>';

				$prod_model = "";
				$stmt->close();
			}
		}
	}

	?>
    <section class="content-header">
        <h1><i class="fa fa-bars"></i> Add Product Gallery</h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active"> Product Gallery</li>
        </ol>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">All the fields marked with * are required</h3>
                        <div class="pull-right">
                            <a href="gallery.php" class="btn btn-sm btn-info"><i class='fa fa-plus'></i> View All</a>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-sm-12">
                                <form role="form" action="gallery.php?act=add" method="post" enctype="multipart/form-data">
                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <label for="prod_model">Product Model</label>
                                            <select name="prod_model" id="prod_model" class="form-control"></select>
                                            <?php if (isset($error[0]) && !empty($error[0])) {print '<p class="text-danger">' . $error[0] . '</p>';}?>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="form-group col-md-10">
                                            <label for="file">Image (max-size:1MB,min-width:1600px and min-height:900px)</label>
                                            <input type="file" name="file" accept=".jpg, .png, .jpeg">
                                            <?php if (isset($error[2]) && !empty($error[2])) {print '<p class="text-danger">' . $error[2] . '</p>';}?>
                                            <?php if (isset($error[3]) && !empty($error[3])) {print '<p class="text-danger">' . $error[3] . '</p>';}?>
                                            <?php if (isset($error[4]) && !empty($error[4])) {print '<p class="text-danger">' . $error[4] . '</p>';}?>
                                            <?php if (isset($error[5]) && !empty($error[5])) {print '<p class="text-danger">' . $error[5] . '</p>';}?>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <div id="bgPic"></div>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary" name="quality">Save</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
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

		$stmt = $conn->prepare("SELECT pg.*, pm.title FROM prod_gallery pg LEFT JOIN prod_model pm ON pg.prod_model=pm.id WHERE pg.id=?");
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
							$stmt = $conn->prepare("UPDATE prod_gallery SET prod_model=?, image=? WHERE id=?");
							$stmt->bind_param("isi", $prod_model, $newFileName, $id);
							if ($stmt->execute()) {
								print '<div class="alert alert-success alert-dismissable">
                                    <button class="close" type="button" data-dismiss="alert" aria-hidden="true">×</button>
                                    Success, product gallery is updated successfully.
                                </div>';
							}
						}
					}
				} else {
					$stmt = $conn->prepare("UPDATE prod_gallery SET prod_model=? WHERE id=?");
					$stmt->bind_param("ii", $prod_model, $id);
					if ($stmt->execute()) {
						print '<div class="alert alert-success alert-dismissable">
                                    <button class="close" type="button" data-dismiss="alert" aria-hidden="true">×</button>
                                    Success, product model is updated successfully.
                                </div>';
					}
				}
			}
		}
	}

	?>
    <section class="content-header">
        <h1><i class="fa fa-bars"></i> Edit Product Gallery</h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Edit Product Gallery</li>
        </ol>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">All the fields marked with * are required</h3>
                        <div class="pull-right">
                            <a href="gallery.php" class="btn btn-sm btn-info"><i class='fa fa-list'></i> View All</a>
                            <a href="gallery.php?act=add" class="btn btn-sm btn-info"><i class='fa fa-plus'></i> Add New</a>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-sm-12">
                                <form role="form" action="gallery.php?act=edit&id=<?php print $id;?>" method="post" enctype="multipart/form-data">
                                    <div class="row">
                                        <div class="form-group col-md-4">
                                            <label for="prod_model">Product Model*</label>
                                            <select name="prod_model" id="prod_model" class="form-control">
                                            	<option value="<?php echo $prod_model; ?>"><?php echo $title; ?></option>
                                            </select>
                                            <?php if (isset($error[0]) && !empty($error[0])) {print '<p class="text-danger">' . $error[0] . '</p>';}?>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <label for="file">Image (max-size:1MB,min-width:1600px and min-height:900px)</label>
                                            <input type="file" value="" name="file" placeholder="Event Image" accept=".jpg, .jpeg, png" />
                                            <?php if (isset($error[2]) && !empty($error[2])) {print '<p class="text-danger">' . $error[2] . '</p>';}?>
                                            <?php if (isset($error[3]) && !empty($error[3])) {print '<p class="text-danger">' . $error[3] . '</p>';}?>
                                            <?php if (isset($error[4]) && !empty($error[4])) {print '<p class="text-danger">' . $error[4] . '</p>';}?>
                                            <?php if (isset($error[5]) && !empty($error[5])) {print '<p class="text-danger">' . $error[5] . '</p>';}?>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <div id="bgPic">
                                                <?php if (isset($event_pic) && !empty($event_pic)) {print '<img style="max-width: 100px;" src="img/gallery/thumb/' . $event_pic . '">';}?>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary" name="editQuality" id="save">Save</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php
break;
default:
	?>
    <section class="content-header">
        <h1><i class="fa fa fa-bars fa-fw"></i>Manage Product Gallery</h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Manage Product Gallery</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12 col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">List of all galleries</h3>
                        <div class="pull-right">
                            <a href="gallery.php?act=add" class="btn btn-sm btn-info"><i class='fa fa-plus'></i> Add New</a>
                        </div>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body no-padding">
                        <table class="table table-hover" id="example" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Product Category</th>
                                    <th>Product Name</th>
                                    <th>Product Model</th>
                                    <th>Image</th>
                                    <th>Manage</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php
break;
}
?>
    </div>
    <!-- /.content-wrapper -->
    <?php include 'inc/footer.php';?>
</div>
<!-- ./wrapper -->
<!-- jQuery 2.1.4 -->
<script src="plugins/jQuery/jQuery-2.1.4.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
$.widget.bridge('uibutton', $.ui.button);
</script>
<!-- Bootstrap 3.3.5 -->
<script src="bootstrap/js/bootstrap.min.js"></script>
<!-- Slimscroll -->
<script src="plugins/slimScroll/jquery.slimscroll.min.js"></script>
<!-- FastClick -->
<script src="plugins/fastclick/fastclick.min.js"></script>
<!-- AdminLTE App -->
<script src="dist/js/app.min.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="dist/js/demo.js"></script>
<!-- Select2 -->
<script src="plugins/select2/select2.full.min.js"></script>
<script src="plugins/input-mask/jquery.inputmask.js"></script>
<script src="plugins/input-mask/jquery.inputmask.date.extensions.js"></script>
<script src="plugins/datepicker/bootstrap-datepicker.js"></script>
<!-- datatables -->
<script src="plugins/datatables/jquery.dataTables.min.js"></script>
<script src="plugins/datatables/dataTables.bootstrap.min.js"></script>

<script type="text/javascript">

	$("#prod_model").select2({
		placeholder: "Search Product Model...",
		width: '100%',
		ajax: {
			url: "ajax/handle-datatable.php",
			type: 'POST',
			dataType: 'json',
			delay: 250,
			data: function(params) {
				return {
					searchTerm: params.term, // search term
					page: params.page,
					act: "findProdModel"
				};
			},
			processResults: function(data, params) {
				params.page = params.page || 1;
				return {
					results: data.items,
					pagination: {
						more: (params.page * 10) < data.count_filtered
					}
				};
			},
			cache: false
		}
	});

    $('#example').DataTable({
        "processing": true,
        "serverSide": true,
        'serverMethod': 'post',

        "ajax": {
            "url": "ajax/handle-datatable.php",
            "data": {"act": "galleryList"}
        },
        "columns": [
            {"data": "id"},
            {"data": "prod_cat"},
            {"data": "prod"},
            {"data": "prod_model"},
            {"data": "image"},
            {"data": "edit"}
        ],
        "columnDefs": [{
            "targets": -1,
            "orderable": false
        }]
});
</script>
</body>
</html>