<table>
    <thead>
        <tr>
            <th colspan="6">Bảng kết quả nhiệm vụ được giao - {{ $className }}</th>
        </tr>
    </thead>
    <tbody>
    @foreach($assignedTasks as $task)
        {{-- Task header row --}}
        <tr>
            <td><strong>Nhiệm vụ:</strong></td>
            <td colspan="4"><strong>{{ $task['name'] }}</strong></td>
            <td>{{ $task['duration'] }}</td>
        </tr>
        <tr>
            <td><strong>STT</strong></td>
            <td><strong>Họ và tên</strong></td>
            <td><strong>Điểm</strong></td>
            <td><strong>Đúng / Tổng</strong></td>
            <td><strong>Thời gian</strong></td>
            <td><strong>Xếp hạng</strong></td>
        </tr>
        @php $ranking = $rankingByTask[$task['practice_id']] ?? []; @endphp
        @forelse($ranking as $row)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $row['name'] }}</td>
            <td>{{ $row['point_text'] ?: 'Chưa làm' }}</td>
            <td>{{ $row['correct_answers_text'] ?? '' }}</td>
            <td>{{ $row['time_text'] ?: '' }}</td>
            <td>{{ $row['point'] > 0 ? $row['rank'] : '' }}</td>
        </tr>
        @empty
        <tr><td colspan="6">Chưa có dữ liệu</td></tr>
        @endforelse
        {{-- blank separator row --}}
        <tr><td colspan="6"></td></tr>
    @endforeach
    </tbody>
</table>
