<table>
    <thead>
        <tr>
            <th colspan="5">Bảng kết quả luyện tập tự do - {{ $className }}</th>
        </tr>
        <tr>
            <th>STT</th>
            <th>Họ và tên</th>
            <th>Tổng sao</th>
            <th>Thời gian (mm:ss)</th>
            <th>Chi tiết</th>
        </tr>
    </thead>
    <tbody>
    @foreach($students as $index => $student)
        @php
            $totalStar = collect($student['best_points_per_practice'] ?? [])->sum('star_count');
            $totalTime = collect($student['best_points_per_practice'] ?? [])
                ->where('type', 1)->sum('time');
            $mm = floor($totalTime / 60);
            $ss = $totalTime % 60;
            $timeText = $totalTime > 0 ? sprintf('%02d:%02d', $mm, $ss) : '';
        @endphp
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $student['name'] }}</td>
            <td>{{ $totalStar }}</td>
            <td>{{ $timeText }}</td>
            <td></td>
        </tr>
        {{-- detail rows per practice --}}
        @foreach($student['best_points_per_practice'] ?? [] as $pt)
        <tr>
            <td></td>
            <td style="padding-left:20px">{{ data_get($pt, 'practice_infos.name', '') }}</td>
            <td>{{ $pt['star_count'] }}</td>
            <td>@php $s=$pt['time']??0; echo $s>0?sprintf('%02d:%02d',floor($s/60),$s%60):''; @endphp</td>
            <td>{{ $pt['type'] == 1 ? 'Luyện tập' : 'Lý thuyết' }}</td>
        </tr>
        @endforeach
        <tr><td colspan="5"></td></tr>
    @endforeach
    </tbody>
</table>
