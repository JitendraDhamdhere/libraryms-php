<?php
session_start();
error_reporting(0);
include('includes/config.php');

if (strlen($_SESSION['login']) == 0) {
    header('location:index.php');
} else {

?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <title>Online Library Management System | Issued Books</title>
    
    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/js/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    
    <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css' />
</head>

<body>
    <?php include('includes/header.php'); ?>

    <div class="content-wrapper">
        <div class="container">
            <div class="row pad-botm">
                <div class="col-md-12">
                    <h4 class="header-line">Manage Issued Books</h4>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <input type="text" id="searchTerm" class="form-control" placeholder="Search by Book Name or ISBN..." onkeyup="filterBooks()">
                </div>
                <div class="col-md-6">
                    <select id="categoryFilter" class="form-control" onchange="filterBooks()">
                        <option value="">All Categories</option>
                        <?php
                        $catQuery = $dbh->prepare("SELECT * FROM tblcategory");
                        $catQuery->execute();
                        $categories = $catQuery->fetchAll(PDO::FETCH_OBJ);
                        foreach ($categories as $category) {
                            echo "<option value='" . htmlentities($category->CategoryName) . "'>" . htmlentities($category->CategoryName) . "</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <br>

            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">Issued Books</div>
                        <div class="panel-body">
                            <?php
                            $sql = "SELECT tblbooks.BookName, tblcategory.CategoryName, tblauthors.AuthorName, tblbooks.ISBNNumber, 
                                        tblbooks.BookPrice, tblbooks.id as bookid, tblbooks.bookImage, tblbooks.isIssued, 
                                        tblbooks.bookQty, COUNT(tblissuedbookdetails.id) AS issuedBooks, 
                                        COUNT(tblissuedbookdetails.RetrunStatus) AS returnedbook 
                                    FROM tblbooks
                                    LEFT JOIN tblissuedbookdetails ON tblissuedbookdetails.BookId = tblbooks.id
                                    LEFT JOIN tblauthors ON tblauthors.id = tblbooks.AuthorId
                                    LEFT JOIN tblcategory ON tblcategory.id = tblbooks.CatId
                                    GROUP BY tblbooks.id";
                            $query = $dbh->prepare($sql);
                            $query->execute();
                            $results = $query->fetchAll(PDO::FETCH_OBJ);
                            $cnt = 1;

                            if ($query->rowCount() > 0) {
                                foreach ($results as $result) { ?>

                                    <div class="col-md-4 book-card" 
                                        data-name="<?php echo strtolower(htmlentities($result->BookName)); ?>"
                                        data-isbn="<?php echo strtolower(htmlentities($result->ISBNNumber)); ?>"
                                        data-category="<?php echo strtolower(htmlentities($result->CategoryName)); ?>"
                                        style="height: 350px;">
                                        
                                        <table class="table table-bordered">
                                            <tr>
                                                <td rowspan="2"><img src="admin/bookimg/<?php echo htmlentities($result->bookImage); ?>" width="120"></td>
                                                <th>Book Name</th>
                                                <td><?php echo htmlentities($result->BookName); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Author</th>
                                                <td><?php echo htmlentities($result->AuthorName); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Category</th>
                                                <td colspan="2"><?php echo htmlentities($result->CategoryName); ?></td>
                                            </tr>
                                            <tr>
                                                <th>ISBN Number</th>
                                                <td colspan="2"><?php echo htmlentities($result->ISBNNumber); ?></td>
                                            </tr>
                                            <tr>
                                                <th> Book Quantity</th>
                                                <td colspan="2"><?php echo htmlentities($result->bookQty); ?></td>
                                            </tr>
                                            <tr>
                                                <th> Available Book Quantity</th>
                                                <td colspan="2"><?php echo htmlentities($result->bookQty - ($result->issuedBooks - $result->returnedbook)); ?></td>
                                            </tr>
                                        </table>
                                    </div>

                            <?php
                                    $cnt = $cnt + 1;
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <?php include('includes/footer.php'); ?>

    <script src="assets/js/jquery-1.10.2.js"></script>
    <script src="assets/js/bootstrap.js"></script>
    <script src="assets/js/dataTables/jquery.dataTables.js"></script>
    <script src="assets/js/dataTables/dataTables.bootstrap.js"></script>
    <script src="assets/js/custom.js"></script>

    <script>
        function filterBooks() {
            let searchTerm = document.getElementById("searchTerm").value.toLowerCase();
            let categoryFilter = document.getElementById("categoryFilter").value.toLowerCase();
            let bookCards = document.querySelectorAll(".book-card");

            bookCards.forEach(function (book) {
                let bookName = book.getAttribute("data-name");
                let isbn = book.getAttribute("data-isbn");
                let category = book.getAttribute("data-category");

                let matchesSearch = bookName.includes(searchTerm) || isbn.includes(searchTerm);
                let matchesCategory = (categoryFilter === "" || category === categoryFilter);

                if (matchesSearch && matchesCategory) {
                    book.style.display = "block";
                } else {
                    book.style.display = "none";
                }
            });
        }
    </script>

</body>
</html>
<?php } ?>
