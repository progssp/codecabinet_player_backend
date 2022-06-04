<?php
		
		$multiplier = 4;
		$size = 1024 * $multiplier;
		for($i = 1; $i <= $size; $i++) {
			echo "." . "<br/>";
		}
        ob_flush();
	    flush();
		sleep(5);
		echo "Hello World";
	?>