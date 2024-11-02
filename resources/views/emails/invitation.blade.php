<p>
    Hai {{ $user->name }}, kamu diundang oleh {{ $booking->user->name }} untuk hadir ke {{ $booking->description }}
    pada waktu {{ $booking->date }} {{ $booking->start_time }} - {{ $booking->end_time }}.
</p>