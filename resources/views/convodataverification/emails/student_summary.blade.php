<!DOCTYPE html>
<html>

<head>
    <title>Student Summary Report</title>
    
</head>

<body>
    <div class="container">
        <h1>Hello Admin,</h1>
        <p>Here is summary report till date:</p>
        <ul>
            @foreach ($all_count as $status => $count)
            <li><strong>{{ ucfirst($status) }}:</strong> {{ $count }}</li>
            @endforeach
        </ul>
        <?php 
        $total_count = array_sum($all_count);
        $total_registration_complete = (int) ($all_count['student re-acknowledged new data as correct, Payment is completed and preview pdf is approved'] ?? 0) +
                                       (int) ($all_count['student acknowledge all data as correct, Payment is completed and preview pdf is approved'] ?? 0);
        ?>
        <h2><strong>Total Student Count:</strong> {{ $total_count }}</h2>
        <h2><strong>Total Registration Completed :</strong> {{ $total_registration_complete  }}</h2>

        <h3><strong>Collection Mode:</strong></h3>
        <ul>
            {{-- <li><strong>Attending Convocation:</strong> {{ $collection_mode['attending_convocation_registration_completed'] }} ({{ $collection_mode['attending_convocation_all'] }})</li>
            <li><strong>Accompanying People Count:</strong>{{ @$collection_mode['no_of_people_accompanied_registration_completed'] }} ({{ @$collection_mode['no_of_people_accompanied_all'] }})</li> --}}
            <li><strong>Collecting by Post (India):</strong> {{ $collection_mode['by_post_india_registration_completed'] }} ({{ $collection_mode['by_post_india_all'] }})</li>
            <li><strong>Collecting by Post (International):</strong> {{ $collection_mode['by_post_international_registration_completed'] }} ({{ $collection_mode['by_post_international_all'] }})</li>
        </ul>
        @include('convodataverification.emails.email_footer')
    </div>
</body>

</html>
