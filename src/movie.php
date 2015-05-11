<html>
    <form method="POST" id="add" action="movie.php">
        <label>Movie Title:</label>
        <input type="text" name="title" maxlength="255"/>
        <label>Movie Category:</label>
        <input type="text" name="category" maxlength="255"/>
        <label>Movie Length (in minutes):</label>
        <input type="number" name="length" min="1" max="400"/>
        <input type="submit" name="addItem" value="Submit"/>
    </form>
    <form method="POST" id="allGone" action="movie.php">
        <input type="submit" name="deleteAll" value="Delete Inventory"/>
    </form>
    <table border="2" id="videos">
        <tr>
            <th>Id</th>
            <th>Name</th>
            <th>Category</th>
            <th>Length</th>
            <th>Rented</th>
        </tr>
    </table>
    

</html>
<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);

//This was taken from the access code snippet found on https://secure.onid.oregonstate.edu/cgi-bin/my to connect to the database

$connect = new mysqli("oniddb.cws.oregonstate.edu", "skeenda-db", "ZAledlGRy9zSTctN", "skeenda-db");
if ($connect->connect_errno) {
    echo "Failed to connect: (" . $connect->connect_errno . ") " . $connect->connect_error;
}
//This adds the item from the html info that is given by the user
if(isset($_POST["addItem"])) {
       
        $title = $_POST["title"];
        $category = $_POST["category"];
        $length = $_POST["length"];
        $rented = 1;
        
        if(!$category) {
	    $newCategory = NULL;
        } 
        if(!$length) {
	    $newLength = NULL;
        }
        //Step 1: Insure that it is prepared
        if (!($stmt = $connect->prepare("INSERT INTO videoInventory(title,category,length,rented) VALUES (?,?,?,?)"))) {
            echo "Failed to prepare: (" . $connect->errno . ") " . $connect->error;
        }
		//step 2: Bind them together
        if (!$stmt->bind_param("ssii", $title,$category,$length,$rented)) {
            echo "Binding parameters failed: (" . $connect->errno . ") " . $connect->error;
        }
		//Step 3: Execute
        if (!$stmt->execute()) {
            echo "Execute failed (" . $connect->errno . ") " . $connect->error;
       
        }
}    
//This checkouts out the movie from the database
if(isset($_POST['checkout'])) {
    $value = 1;
    $result = mysqli_query($connect, "SELECT * FROM videoInventory WHERE rented = '1'");
    while($row = $result->fetch_assoc()){
        if($row['d'] == $_POST['index']) {
            $value = 0;
        }
    }
    
    $sql = "UPDATE videoInventory SET rented = '$value' WHERE d =".$_POST['index'];
    mysqli_query($connect,$sql);
}
//This clears all of the table
if(isset($_POST["deleteAll"])) {
    $sql = "Truncate TABLE videoInventory";
    mysqli_query($connect,$sql);
}
//This deletes the 1 movie from the database, based from http://www.tutorialspoint.com/mysql/mysql-delete-query.htm
if(isset($_POST['delete'])) {
    
    $sql = "DELETE FROM videoInventory WHERE d =".$_POST['index'];  
    mysqli_query($connect,$sql);
   
}
//This sets the filter to allow it to filter through the results, A lot of the ideas for this comes from http://www.w3schools.com/php/php_filter.asp
$filter = mysqli_query($connect,"SELECT * FROM videoInventory");
echo '<form action="movie.php" method="POST">';
$categoryArray = array();
echo '<select name="toFilter">';
while($selection = mysqli_fetch_array($filter)) {
	if(!in_array($selection['category'], $categoryArray)) {
		if($selection['category'] != NULL) {
		array_push($categoryArray, $selection['category']);
		echo "<option value='".$selection['category']."'>".$selection['category']."</option>";
		}	
	}
}
if(!empty($categoryArray)) {
	echo '<option value="all">All</option>';
}
echo '<input type = "submit" value="Filter Results">';
echo '</select>';
echo '</form>';
$fil = 'all';
$getAll = mysqli_query($connect,"SELECT * FROM videoInventory");
if(isset($_POST['toFilter'])) {
    $fil = $_POST['toFilter'];
}
if(($fil == 'all')) {
	while($row = mysqli_fetch_array($getAll)) {
		echo "<tr>";
		echo "<td>".$row['d']."   </td>";
		echo "<td>".$row['title']."   </td>";
		echo "<td>".$row['category']."   </td>";
		echo "<td>".$row['length']."   </td>";
		//Checks out the movie
        if($row['rented'] == 1) {
            echo "<td>"."Available"."   </td>";
        }
         else {
            echo "<td>"."Checked out"."   </td>";   
            }
		
		echo "<td><form method=\"POST\" action=\"movie.php\">";
		echo "<input type=\"hidden\" name=\"index\" value=\"".$row['d']."\">";
		echo "<input type=\"submit\" value=\"delete\" name=\"delete\" >";
		echo "</form>";
		echo "<td><form method=\"POST\" action=\"movie.php\">";
		echo "<input type=\"hidden\" name=\"index\" value=\"".$row['d']."\">";
		echo "<input type=\"submit\" value=\"CheckIn/Checkout\" name=\"checkout\">";
		echo "</form>";
		echo "</tr>";
		}
}
//This makes the screen show the filtered results of the table
else {
    while($row = mysqli_fetch_array($getAll)) {
        if($row['category'] == $fil){
		echo "<tr>";
		echo "<td>".$row['d']."   </td>";
		echo "<td>".$row['title']."   </td>";
		echo "<td>".$row['category']."   </td>";
		echo "<td>".$row['length']."   </td>";
		//checks out the movie
        if($row['rented'] == 1) {
            echo "<td>"."Available"."   </td>";
        }
         else {
            echo "<td>"."Checked out"."   </td>";   
            }
		
		echo "<td><form method=\"POST\" action=\"movie.php\">";
		echo "<input type=\"hidden\" name=\"index\" value=\"".$row['d']."\">";
		echo "<input type=\"submit\" value=\"delete\" name=\"delete\" >";
		echo "</form>";
		echo "<td><form method=\"POST\" action=\"movie.php\">";
		echo "<input type=\"hidden\" name=\"index\" value=\"".$row['d']."\">";
		echo "<input type=\"submit\" value=\"CheckIn/Checkout\" name=\"checkout\">";
		echo "</form>";
		echo "</tr>";
        }
    }    
}
    mysqli_close($connect);
?>


