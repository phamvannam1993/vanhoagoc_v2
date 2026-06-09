<table>
    <thead>
    <tr>
        <th colspan="5">{{ $title }}</th>
    </tr>
    <tr>
        <th>Xếp hạng</th>
        <th>Họ và tên</th>
        <th>Điểm (Sao)</th>
        <th>Số câu đúng</th>
        <th>Thời gian làm</th>
    </tr>
    </thead>
    <tbody>
    @foreach($data as $item)
        <tr>
            <td>{{ $item['rank'] }}</td>
            <td>{{ $item['name'] }}</td>
            <td>{{ $item['point_text'] }}</td>
            <td>{{ $item['correct_answers_text'] ?? '' }}</td>
            <td>{{ $item['time_text'] }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
