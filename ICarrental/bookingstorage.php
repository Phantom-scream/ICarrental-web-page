<?php
require_once("storage.php");

class BookingStorage extends Storage {
    public function __construct() {
        parent::__construct(new JsonIO('booking.json'));
    }

    public function findByCarId($carId) {
        return $this->findAll(['car_id' => (int)$carId]);
    }

    public function hasOverlap($carId, $startDate, $endDate) {
        $existingBookings = $this->findByCarId($carId);
    
        foreach ($existingBookings as $booking) {
            $bookingStart = strtotime($booking['start_date']);
            $bookingEnd = strtotime($booking['end_date']);
            $inputStart = strtotime($startDate);
            $inputEnd = strtotime($endDate);
    
            if ($inputStart <= $bookingEnd && $inputEnd >= $bookingStart) {
                return true;
            }
        }
    
        return false;
    }
    
}
?>