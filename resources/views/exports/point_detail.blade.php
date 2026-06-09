<table>
    <thead>
        <tr>
            <th colspan="3">{{ $title }} - {{ $studentName }} - {{ $nameApp }}</th>
        </tr>
        <tr>
            <th>Câu</th>
            <th>Kết quả</th>
            <th>Thời gian</th>
        </tr>
    </thead>
    <tbody>
    @foreach($rows as $index => $row)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ data_get($row, 'star_count') == 1 ? 'Chính xác' : 'Chưa thực hiện' }}</td>
            <td>{{ data_get($row, 'time_text') }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
