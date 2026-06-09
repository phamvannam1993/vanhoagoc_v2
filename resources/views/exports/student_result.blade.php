<table>
    <thead>
        <tr>
            <th colspan="7">Luyện tập tự do - {{ $studentName }} - {{ $className }}</th>
        </tr>
        <tr>
            <th>STT</th>
            <th>Ngày</th>
            <th>Nội dung</th>
            <th>Điểm lý thuyết</th>
            <th>Điểm luyện tập</th>
            <th>Thời gian làm</th>
            <th>Điểm tổng hợp</th>
            <th>Xếp hạng</th>
        </tr>
    </thead>
    <tbody>
    @foreach($rows as $index => $row)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $row['day_create'] ?? '' }}</td>
            <td>{{ $row['name'] ?? '' }}</td>
            <td>{{ $row['point_text_2'] ?? '' }}</td>
            <td>{{ $row['point_text_1'] ?? '' }}</td>
            <td>{{ $row['time_text'] ?? '' }}</td>
            <td>{{ $row['total_point'] ?? '' }}</td>
            <td>{{ $row['rank'] ?? '' }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
