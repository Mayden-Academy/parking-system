<?php

//function d ($var) {
//    die(var_dump($var));
//}

/**
 * Created by PhpStorm.
 * User: benmorris
 * Date: 14/11/2016
 * Time: 14:35
 */
class Carpark
{
    const CARPARK_TABLE_NAME = 'carpark';

    private $pdo;
    private $id;
    private $name;
    private $capacity;
    private $isVisitor;

    public function __construct(PDO $pdo, $name) {

        $statement = $pdo->prepare('SELECT * FROM `' . self::CARPARK_TABLE_NAME . '` WHERE `name` = ?;');
        $statement->execute(array($name));
        $result = $statement->fetch();
        if (!empty($result)) {
            $this->pdo = $pdo;
            $this->id = $result['id'];
            $this->name = $name;
            $this->capacity = $result['capacity'];
            $this->isVisitor = $result['isVisitor'];
            return $this;
        } else {
            throw new Exception ('Carpark with name ' . $name . ' does not exist in pdo.');
        }

    }

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getCapacity() {
        return $this->capacity;
    }

    public function isVisitor() {
        return $this->isVisitor;
    }

    //just handling staff bookings at first
    // assume format 'YYYY-MM-DD HH:MM' so split on space
    public function getAvailability($dateTimeFrom, $dateTimeTo) {
        $bookingManager = new BookingManager($this->pdo);
        $bookings = $bookingManager->getBookings($this->getId(), $dateTimeFrom, $dateTimeTo);

        // Handling datetime case
        if ($this->isVisitor()) {
            // Test that the input was provided in correct format for carpark type
            if (count(explode(' ', $dateTimeFrom)) < 2) {
                throw new Exception('Availability in days for visitor!');
            }
            $startTime = strtotime(explode(' ', $dateTimeFrom)[1]);
            $endTime = strtotime(explode(' ', $dateTimeTo)[1]);
            $time = $startTime;
            $bookingsAtTime = [];
            while ($time < $endTime) {
                $bookingsAtTime[$time] = 0;
                foreach ($bookings as $booking) {
                    if (strtotime(explode(' ', $booking['from'])[1]) <= $time && $time < strtotime(explode(' ', $booking['to'])[1])) {
                        $bookingsAtTime[$time]++;
                    }
                }

            $time += 15 * 60;
            }
            return  $this->getCapacity() - max($bookingsAtTime);
        } else {
            // Test that the input was provided in correct format for carpark type
            if (count(explode(' ', $dateTimeFrom)) > 1) {
                throw new Exception('Availability in hours for staff!');
            }
            $startDay = explode(' ', $dateTimeFrom)[0];
            $endDay = explode(' ', $dateTimeTo)[0];
            $day = $startDay;
            $bookingsOnDay = [];
            while ($day <= $endDay) {
                $bookingsOnDay[$day] = 0;
                foreach ($bookings as $booking) {
                    if (self::check_in_range(explode(' ', $booking['from'])[0], explode(' ', $booking['to'])[0], $day)) {
                        $bookingsOnDay[$day]++;
                    }
                }
                $date = strtotime("+1 day", strtotime($day));
                $day = date("Y-m-d", $date);
            }
            return  $this->getCapacity() - max($bookingsOnDay);

        }



    }

    public static function check_in_range($start_date, $end_date, $date_from_user)
    {
        //d([$start_date, $end_date, $date_from_user]);

        // Convert to timestamp
        $start_ts = strtotime($start_date);
        $end_ts = strtotime($end_date);
        $user_ts = strtotime($date_from_user);

        // Check that user date is between start & end
        return (($user_ts >= $start_ts) && ($user_ts <= $end_ts));
    }



}