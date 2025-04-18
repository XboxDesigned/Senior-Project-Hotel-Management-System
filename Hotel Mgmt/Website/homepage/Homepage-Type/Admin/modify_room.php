<?php
 require_once('../../Website/inc/db_connect.php');
 
 $status = session_status();
 if ($status == PHP_SESSION_NONE) {
     session_start();
 }
 
 $queryRooms = "
 SELECT
   room_num,
   room_type,
   room_status,
   rate_plan
 FROM
   rooms
 ";
 $statementRooms = $db->prepare($queryRooms);
 $statementRooms->execute();
 $items = $statementRooms->fetchAll();
 $statementRooms->closeCursor();
 ?>
 <!DOCTYPE HTML>
 <html>
 <head>
     <title>Rooms</title>
     <link rel="stylesheet" type="text/css" href="../inc/homepage_main.css">
     <style>
         td {
             padding: 10px 20px;
         }
 
         table {
             margin-left: 220px;
             width: 80%;
             max-width: 1000px;
             border-collapse: collapse;
         }
 
         th, td {
             padding: 12px;
             border: 1px solid #ccc;
             text-align: left;
         }
 
         button {
             margin-right: 5px;
         }
 
         @media (max-width: 900px) {
             table {
                 width: 90%;
                 margin-left: auto;
                 margin-right: auto;
                 overflow-x: auto;
                 display: block;
             }
         }
     </style>
 </head>
 <main>
 <body>
     <div id="data">
         <h3>Room List:</h3>
     </div>
     <br>
     
 
 
     <table>
         <tr>
             <th>Room Number</th>
             <th>Room Type</th>
             <th>Status</th>
             <th>Rate Plan</th>
             <th>Actions</th>
         </tr>
 
         <?php foreach ($items as $item) : ?>
         <tr>
             <td><?php echo $item['room_num']; ?></td>
             <td><?php echo $item['room_type']; ?></td>
             <td><?php echo $item['room_status']; ?></td>
             <td>$<?php echo number_format($item['rate_plan'], 2); ?></td>
             <td>
                 <button class="modify-btn"><a href="?update_room_id=<?php echo $item['room_num']; ?>" style="color:white; text-decoration:none;">Update Room</a></button>
                 <button class="modify-btn">
                     <a href="?delete_room_id=<?php echo $item['room_num']; ?>" style="color:white; text-decoration:none;">Delete Room</a>
                 </button>
             </td>
         </tr>
         <?php endforeach; ?>
     </table>
 </body>
 </main>
 </html>