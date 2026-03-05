
<div class="container">
    <h3>Payment Response</h3>
    <table class="table table-bordered">
        @foreach($response as $key => $value)
            <tr>
                <th>{{ $key }}</th>
                <td>{{ $value }}</td>
            </tr>
        @endforeach
    </table>
</div>

