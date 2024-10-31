CREATE FUNCTION `followers_count`(`parent_id` INT, `return_value` VARCHAR(1024)) 
                RETURNS VARCHAR(1024)
                BEGIN
                DECLARE rv,q,queue,queue_children2 VARCHAR(1024);
                DECLARE queue_length,front_id,pos INT;
                DECLARE no_of_followers INT;

                SET rv = parent_id;
                SET queue = parent_id;
                SET queue_length = 1;
                SET no_of_followers = 0;

                WHILE queue_length > 0 DO

                SET front_id = FORMAT(queue,0);
                IF queue_length = 1 THEN
                SET queue = '';
                ELSE
                SET pos = LOCATE(',',queue) + 1;
                SET q = SUBSTR(queue,pos);
                SET queue = q;
                END IF;
                SET queue_length = queue_length - 1;

                SELECT IFNULL(qc,'') INTO queue_children2
                FROM (SELECT GROUP_CONCAT(user_id) qc
                FROM wp_referal_users WHERE referral_parent IN (front_id)) A;

                IF LENGTH(queue_children2) = 0 THEN
                IF LENGTH(queue) = 0 THEN
                SET queue_length = 0;
                END IF;
                ELSE
                IF LENGTH(rv) = 0 THEN
                SET rv = queue_children2;
                ELSE
                SET rv = CONCAT(rv,',',queue_children2);
                END IF;
                IF LENGTH(queue) = 0 THEN
                SET queue = queue_children2;
                ELSE
                SET queue = CONCAT(queue,',',queue_children2);
                END IF;
                SET queue_length = LENGTH(queue) - LENGTH(REPLACE(queue,',','')) + 1;
                END IF;
                END WHILE;

                IF(return_value = 'count') THEN
                SELECT count(*) into no_of_followers  FROM wp_referal_users WHERE active = 1 AND FIND_IN_SET(referral_parent, rv );

                RETURN no_of_followers;
                ELSE
                RETURN rv;
                END IF;
                END ;;