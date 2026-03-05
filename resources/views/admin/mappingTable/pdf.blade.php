<!DOCTYPE html>
<html>
<head>
<style>
table, th, td {
  border: 1px solid black;
}
</style>
</head>
<body>
    <h5>Database Name :- @if($db_details){{ $db_details['db_name'] }}@endif</h5> <h5>Table Name:- @if($db_details){{ $db_details['table_name'] }}@endif</h5>
    <table style="width:100%">
        <thead>
            <tr>
                @foreach($columns as $value)
                    <th>{{ ucwords(str_replace('_', ' ', $value)) }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($mapTable as $value)
                <tr>
                @foreach($value as $val) 
                    <td>{{ $val }}</td>
                @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
