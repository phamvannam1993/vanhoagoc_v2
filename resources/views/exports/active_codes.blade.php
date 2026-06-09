<table>
    <thead>
    <tr>
        <th>ID</th>
        <th>Mã kích hoạt</th>
        <th>Giá</th>
        <th>Trạng thái</th>
    </tr>
    </thead>
    <tbody>
    @foreach($active_codes as $key => $active_code)
        <tr>
            <td>{{ $active_code->id }}</td>
            <td>{{ $active_code->code }}</td>
            <td>{{ $active_code->price  }}</td>
            <td>
            @if($active_code->is_used === 0)
                Chưa kích hoạt
            @else
                Đã kích hoạt
            @endif
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
