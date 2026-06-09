<table>
    <thead>
        <tr>
            <th colspan="3">Nhiệm vụ được giao - {{ $className }}</th>
        </tr>
        <tr>
            <th>STT</th>
            <th>Danh sách nhiệm vụ</th>
            <th>Thời hạn</th>
        </tr>
    </thead>
    <tbody>
    @foreach($rows as $index => $row)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $row['name'] ?? '' }}</td>
            <td>{{ $row['duration'] ?? '' }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
