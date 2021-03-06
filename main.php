<?php
    include "db.php";
    // ----------------------------- TRIGGERS ---------------------------------
    if($_POST['message'] == "load-blog-comments") {echo load_blog_comments();}

    if($_POST['message'] == "populate-labels") {echo populate_labels();}
    
    if($_POST['message'] == "new-label") {echo add_label($_POST['name'], $_POST['color']);}

    if($_POST['message'] == "submit-new-post") {echo submit_new_post($_POST['text'], $_POST['moji'], $_POST['label']);}
    
    if($_POST['message'] == "populate-posts-backend") {echo populated_posts_backend();}
    
    if($_POST['message'] == "populate-labels-for-new-post") {echo populate_labels_for_new_post();}

    // ------------------------------ FUNCTIONS --------------------------------
    function tag_recognition($text) {
        
    }
    
    function submit_new_post($text, $moji, $label) {
        $text = tag_recognition($text);
        try {
            $c = connDB(); //establish db connection
            $sql = "SELECT MAX(ID)+1 FROM Comment;";
            $s = $c->prepare($sql);
            $s -> execute();
            if ($max = $s -> fetchColumn()) $id = $max;
            else $id = 1;   
            // $moji = intval(substr($moji, 2, strlen($moji)));
            $sql = "INSERT INTO Comment (ID, Moji, Text, Timestamp, Label_ID) VALUES (".$id.", ".$moji.", '".$text."', NOW(), ".$label.");";
            $c -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $c -> exec($sql);

            $c = null; //close connection
        } catch (PDOException $e) {return $e;}
        return $text;
    }
    
    function datetime_tonumbers_date($datetime) {
        $year = substr($datetime, 0, 4);
        $month = substr($datetime, 5, 2);
        $day = substr($datetime, 8, 2);
        return intval($year.$month.$day);
    }

    function reformat_numberdate($date) {
        $str = strval($date);
        $months = ["January", "February", "March", "April", "May", " June", "July", "August", "September", "October", "November", "December"];
        return $months[(intval(substr($str, 4, 2)))-1]." ".substr($str, 6, 2).", ".substr($str, 0, 4);
    }

    function load_blog_comments() {
        try {
            $data = '';
            $c = connDB();
            $sql = "SELECT Timestamp FROM Comment ORDER BY Timestamp DESC LIMIT 1;";
            $s = $c -> prepare($sql);
            $s -> execute();
            $t = $s -> fetch(PDO::FETCH_ASSOC);
            $date = datetime_tonumbers_date($t['Timestamp']);
            $data .= '<h3 class = "blog-group-date">'.reformat_numberdate($date).'</h3>';
            // sql : desc = oldest date first, asc = neweset date first
            $sql = "SELECT c.ID, c.Moji, c.Text, c.Timestamp, l.Name, l.Color FROM Comment as c JOIN Label as l WHERE c.Label_ID = l.ID ORDER BY c.Timestamp DESC;";
            $s = $c -> prepare($sql);
            $s -> execute();
            while($r = $s -> fetch(PDO::FETCH_ASSOC)) {
                if ($date != datetime_tonumbers_date($r['Timestamp'])) {
                    $date = datetime_tonumbers_date($r['Timestamp']);
                    $data .= '<h3 class = "blog-group-date">'.reformat_numberdate($date).'</h3>';
                }

                $data .=
                '
                    <div class = "post">
                        <div class = "details">
                            <p class = "moji">&#'.$r['Moji'].';</p>
                            <p class = "likes"> <i class="fa-solid fa-heart"></i> 4 </p>
                            <p class = "timestamp">'.substr($r['Timestamp'],11,5).'</p>
                        </div>
                        <div class = "text" style = "border-left: 5px solid '.$r['Color'].'">
                            <p>'.$r['Text'].'</p>
                        </div>
                    </div>
                ';
            }  
            // $c = null;
        } catch (PDOException $e) {return $e;}
        return $data;
    }

    function populate_labels() {
        try {
            $c = connDB(); //establish connection
            $sql = "SELECT ID, Name, Color FROM Label";
            $s = $c -> prepare($sql);
            $s -> execute();
        
        } catch (PDOException $e) {return $e;}
        $data = "";
        while($r = $s -> fetch(PDO::FETCH_ASSOC)) {
            $data .=
            '
                <div class = "label-container">
                    <h4 style = "background-color: '.$r['Color'].'; color: '.$r['Color'].';"> * </h4> 
                    <h5> '.$r['Name'].' </h5>
                </div>
            ';
        }
        return $data;

    }

    function add_label($name, $color) {
        try {
            $c = connDB(); //establish db connection
            $sql = "SELECT MAX(ID)+1 FROM Label;";
            $s = $c -> prepare($sql);
            $s -> execute();
            if ($max = $s -> fetchColumn()) $id = $max;
            else $id = 1;   
            
            $sql = "INSERT INTO Label (ID, Name, Color) VALUES (".$id.", '".$name."', '".$color."');";
            $c -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $c -> exec($sql);

            $c = null; //close connection
        } catch (PDOException $e) {return $e;}

        return populate_labels();
    }

    function populated_posts_backend() {
        try {
            $data = '';
            $c = connDB();
            $sql = "SELECT Timestamp FROM Comment ORDER BY Timestamp DESC LIMIT 1;";
            $s = $c -> prepare($sql);
            $s -> execute();
            $t = $s -> fetch(PDO::FETCH_ASSOC);
            $date = datetime_tonumbers_date($t['Timestamp']);
            $data .= '<h3 class = "blog-group-date">'.reformat_numberdate($date).'</h3>';
            // sql : desc = oldest date first, asc = neweset date first
            $sql = "SELECT c.ID, c.Moji, c.Text, c.Timestamp, l.Name, l.Color FROM Comment as c JOIN Label as l WHERE c.Label_ID = l.ID ORDER BY c.Timestamp DESC;";
            $s = $c -> prepare($sql);
            $s -> execute();
            while($r = $s -> fetch(PDO::FETCH_ASSOC)) {
                if ($date != datetime_tonumbers_date($r['Timestamp'])) {
                    $date = datetime_tonumbers_date($r['Timestamp']);
                    $data .= '<h3 class = "blog-group-date">'.reformat_numberdate($date).'</h3>';
                }

                $data .=
                '
                    <div class = "edit-post">
                        <div class = "details">
                            <p class = "moji">&#'.$r['Moji'].'</p>
                            <div class = "actions"> 
                                <button class = "likes"> <i class = "fa fa-heart" aria-hidden = "true"></i> &nbsp; 4 </button>
                                <button class = "edit"> <i class = "fa fa-pencil"></i> &nbsp; Edit </button>
                                <button class = "delete"> <i class = "fa fa-times"></i> &nbsp; Delete </button>
                            </div>
                            <p class = "timestamp">'.substr($r['Timestamp'],11,5).'</p>
                        </div>
                        <div class = "text" style = "border-left: 5px solid '.$r['Color'].'">
                            <p>'.$r['Text'].'</p>
                        </div>
                    </div>
                ';
            }  
            // $c = null;
        } catch (PDOException $e) {return $e;}
        return $data;
    }

    function populate_labels_for_new_post() {
        $checked = "checked";
        try {
            $data = '';
            $c = connDB();
            $sql = "SELECT ID, Name, Color FROM Label ORDER BY ID ASC;";
            $s = $c -> prepare($sql);
            $s -> execute();
            while($r = $s -> fetch(PDO::FETCH_ASSOC)) {
                $data .=
                '
                    <input type = "radio" name = "label" id = "label-'.$r['ID'].'" value = "'.$r['ID'].'" class = "label-choice" '.$checked.'/>
                    <label for = "label-'.$r['ID'].'" style = "border: 3px solid '.$r['Color'].';"> '.$r['Name'].' </label>
                ';
                if($checked == "checked") $checked = "";
            }  
            // $c = null;
        } catch (PDOException $e) {return $e;}
        return $data;
    }


?>