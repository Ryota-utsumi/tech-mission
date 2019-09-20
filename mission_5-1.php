<html>
<head>
   <meta charset="utf-8">
</head>
<body>
<?php
 session_start();
 if(isset($_SESSION['number'])==true&&isset($_SESSION['rename'])==true&&isset($_SESSION['recomment'])==true){
	 $number = $_SESSION['number'];
	 $name = $_SESSION['rename'];
	 $comment = $_SESSION['recomment'];
	 unset($_SESSION['number']);
	 unset($_SESSION['rename']);
	 unset($_SESSION['recomment']);
 }
?>
<form method="POST" action="mission_5-1.php">
	お名前：<input type="text" name="name" value="<?php if(isset($name)){ echo $name;} ?>"><br>
	コメント：<textarea name="comment" ><?php if(isset($comment)){ echo $comment;} ?></textarea><br>
	パスワード：<input type="password" name="pass"><br>
	<input type="hidden" name="changenum" value="<?php if(isset($number)){ echo $number;} ?>"><br>
	<input type="submit" value="送信" name="button1">
</form>
<br>
<br>
<form method="POST" action="mission_5-1.php">
	削除対象番号：<input type="number" name="number"><br>
	パスワード:<input type="password" name="delpass"><br>
	<input type="submit" value="削除" name="button2">
</form>
<form method="POST" action="mission_5-1.php">
	編集対象番号：<input type="number" name="change"><br>
	パスワード：<input type="password" name="changepass"><br>
	<input type="submit" value="編集" name="button3"><hr>
</form>

<?php
	//データベース接続
	$dsn = 'データベース名';
	$user = 'ユーザー名';
	$password = 'パスワード';
	$pdo = new PDO($dsn,$user,$password,array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_WARNING));
	//テーブル作成
	$sql = "CREATE TABLE IF NOT EXISTS mission"
	." ("
	. "id INT AUTO_INCREMENT PRIMARY KEY,"
	. "name char(32),"
	. "comment TEXT,"
	. "time DATETIME,"
	. "password char(32)"
	.");";
	$stmt = $pdo->query($sql);
	
   if(isset($_POST['button1'])){
   	//新規投稿用処理
      if(empty($_POST['changenum'])){  
	  	   if(empty($_POST['comment'])||empty($_POST['name'])||empty($_POST['pass'])){  //フォームが空の時
	    	   echo "フォームが空です。";
	   	}else{  //以下投稿用処理
	 	      $putname = $_POST['name'];
	 	      $putcomment = $_POST['comment'];
	 	      $putpass = $_POST['pass'];
	         $puttime = date("Y/m/d H:i:s");
				//データの挿入 //SQLインジェクションのリスクを排して挿入できる
				$sql = $pdo -> prepare("INSERT INTO mission (name,comment,time,password) VALUES (:name,:comment,:time,:password)");
				$sql -> bindParam(':name', $putname, PDO::PARAM_STR);
				$sql -> bindParam(':comment', $putcomment, PDO::PARAM_STR);    
				$sql -> bindParam(':time', $puttime, PDO::PARAM_STR);
				$sql -> bindParam(':password', $putpass, PDO::PARAM_STR);
				$sql -> execute();
				//データの表示
	         $sql = 'SELECT * FROM mission';
				$stmt = $pdo->query($sql);
				$results = $stmt->fetchAll();
				foreach ($results as $row){
				//$rowの中にはテーブルのカラム名が入る
					echo $row['id'].' '.$row['name'].' '.$row['comment'].' '.$row['time'];
					echo '<br>';
				}
         }
         exit;
      }
      //編集投稿用処理
      else{
      	if(empty($_POST['comment'])||empty($_POST['name'])||empty($_POST['pass'])){  //フォームが空の時
	    	   echo "フォームが空です。";
	   	}else{  //以下編集用処理
	   		$id = $_POST['changenum'];
	   		$name = $_POST['name'];
	   		$comment = $_POST['comment'];
	   		$password = $_POST['pass'];
         	$sql = 'update mission set name=:name,comment=:comment,password=:password where id=:id';
				$stmt = $pdo->prepare($sql);
				$stmt->bindParam(':name', $name, PDO::PARAM_STR);
				$stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
				$stmt->bindParam(':password', $password, PDO::PARAM_STR);
				$stmt->bindParam(':id', $id, PDO::PARAM_INT);
				$stmt->execute();
         	//編集後の表示
         	$sql = 'SELECT * FROM mission';
				$stmt = $pdo->query($sql);
				$results = $stmt->fetchAll();
				foreach ($results as $row){
					echo $row['id'].' '.$row['name'].' '.$row['comment'].' '.$row['time'];
					echo '<br>';
				}
	   	}
	   	exit;
      }
   }
   //以下削除処理
   else if(isset($_POST['button2'])){
      if(empty($_POST['number'])||empty($_POST['delpass'])){
         $x="フォームが空です。";
         echo $x;
      }else{
         $deletenum = $_POST['number'];
         $deletepass = $_POST['delpass'];
         //削除指定番号が存在しないとき
         /*
         $sql = 'SELECT * FROM mission';
         $stmt = $pdo->query($sql);
         $stmt->execute();
         $count = $stmt->rowCount();
			if($deletenum<1||$deletenum>$count){
				echo "削除指定番号が存在しません";
				echo '<br>';
				echo '<br>';
				$sql = 'SELECT * FROM mission';
				$stmt = $pdo->query($sql);
				$results = $stmt->fetchAll();
				foreach ($results as $row){
					echo $row['id'].' '.$row['name'].' '.$row['comment'].' '.$row['time'];
					echo '<br>';
				}
				exit;
			}  */
			//パスワード照合部
			$sql = 'SELECT * FROM mission';
         $stmt = $pdo->query($sql);
			$results = $stmt->fetchAll();
			foreach ($results as $row){
				if($deletenum==$row['id']){
					if($deletepass!=$row['password']){
						echo "パスワードが違います。";
						echo "削除できませんでした。";
						echo '<br>';
						echo '<br>';
						$sql = 'SELECT * FROM mission';
						$stmt = $pdo->query($sql);
						$results = $stmt->fetchAll();
						foreach ($results as $row){
							echo $row['id'].' '.$row['name'].' '.$row['comment'].' '.$row['time'];
							echo '<br>';
						}
						exit;
					}
				}
			}
	      //パスワード一致後の処理
	      $sql = 'delete from mission where id=:id';
			$stmt = $pdo->prepare($sql);
			$stmt->bindParam(':id', $deletenum, PDO::PARAM_INT);
			$stmt->execute();
			
			//削除した行以降の番号を繰り上げる処理
			/*
			$sql = 'SELECT * FROM mission';
			$stmt = $pdo->query($sql);
			$results = $stmt->fetchAll();
			foreach ($results as $row){
				if($row['id']>$deletenum){
					$reID = $row['id']-1;
					$sql = 'update mission id=:id where id=:id';
					$stmt = $pdo->prepare($sql);
					$stmt->bindParam(':id', $reID, PDO::PARAM_STR);
					$stmt->execute();
				}
			}*/
			
	      //削除後データの表示
	      $sql = 'SELECT * FROM mission';
			$stmt = $pdo->query($sql);
			$results = $stmt->fetchAll();
			foreach ($results as $row){
				echo $row['id'].' '.$row['name'].' '.$row['comment'].' '.$row['time'];
				echo '<br>';
			}
      }
      exit;
   }
   //編集投稿用フォームに準備する処理
   else if(isset($_POST['button3'])){
   	if(empty($_POST['change'])||empty($_POST['changepass'])){
   		echo "フォームが空です。";
   	}else{
   		$changenum = $_POST['change'];
   		$changepass = $_POST['changepass'];
			//編集指定番号が存在しないとき
			/*
         $sql = 'SELECT * FROM mission';
         $stmt = $pdo->query($sql);
         $stmt->execute();
         $count = $stmt->rowCount();
			if($changenum<1||$changenum>$count){
				echo "編集指定番号が存在しません";
				echo '<br>';
				echo '<br>';
				$sql = 'SELECT * FROM mission';
				$stmt = $pdo->query($sql);
				$results = $stmt->fetchAll();
				foreach ($results as $row){
					echo $row['id'].' '.$row['name'].' '.$row['comment'].' '.$row['time'];
					echo '<br>';
				}
				exit;
			}*/
         //パスワード照合部
         $stmt = $pdo->query($sql);
 			$results = $stmt->fetchAll();
 			foreach ($results as $row){
 				if($changenum==$row['id']){
 					if($changepass!=$row['password']){
 						echo "パスワードが違います。";
 						echo "編集対象の情報取得に失敗しました。";
 						echo '<br>';
 						echo '<br>';
 						$sql = 'SELECT * FROM mission';
 						$stmt = $pdo->query($sql);
 						$results = $stmt->fetchAll();
 						foreach ($results as $row){
 							echo $row['id'].' '.$row['name'].' '.$row['comment'].' '.$row['time'];
 							echo '<br>';
 						}
 						exit;
 					}
 				}
 			}
         //パスワード一致後の処理
         $sql = 'SELECT * FROM mission';
			$stmt = $pdo->query($sql);
			$results = $stmt->fetchAll();
			foreach ($results as $row){
				if($row['id']==$changenum){
					$_SESSION['number'] = $row['id'];
					$_SESSION['rename'] = $row['name'];
					$_SESSION['recomment'] = $row['comment'];
				}
			}
	      header("Location: mission_5-1.php");
	      exit;
   	}
   	exit;
   }
?>


</body>
</html>