<table>
    <thead>
        <tr>
            <th colspan="8">Nhiệm vụ được giao - {{ $studentName }} - {{ $className }}</th>
        </tr>
        <tr>
            <th>STT</th>
            <th>Nội dung</th>
            <th>Thời hạn</th>
            <th>Trạng thái thực hiện</th>
            <th>Điểm (Sao)</th>
            <th>Thời gian làm</th>
            <th>Xếp hạng</th>
            <th>Đánh giá</th>
        </tr>
    </thead>
    <tbody>
    @foreach($rows as $index => $row)
        @php
            $status = data_get($row, 'status');
            $statusLabel = $status ? 'Đã thực hiện' : 'Chưa thực hiện';
            $reviewStatus = data_get($row, 'review_status');
            $reviewLabel = $reviewStatus == 1 ? 'Đạt' : ($reviewStatus == 2 ? 'Không đạt' : 'Đánh giá');
        @endphp
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ data_get($row, 'name') }}</td>
            <td>{{ data_get($row, 'duration') }}</td>
            <td>{{ $statusLabel }}</td>
            <td>{{ data_get($row, 'point_text') }}</td>
            <td>{{ data_get($row, 'time_text') }}</td>
            <td>{{ data_get($row, 'rank') }}</td>
            <td>{{ $reviewLabel }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
