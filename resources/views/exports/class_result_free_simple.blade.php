<table>
    <thead>
        <tr>
            <th colspan="4">Luyện tập tự do - {{ $className }}</th>
        </tr>
        <tr>
            <th>STT</th>
            <th>Họ và tên</th>
            <th>Thành tích (Sao)</th>
            <th>Thời gian làm</th>
        </tr>
    </thead>
    <tbody>
    @foreach($rows as $index => $row)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $row['name'] ?? '' }}</td>
            <td>{{ $row['star_count'] ?? '' }}</td>
            <td>{{ $row['time_text'] ?? '' }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
