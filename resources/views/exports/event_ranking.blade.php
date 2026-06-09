<table>
    <thead>
    <tr>
        <th>STT</th>
        <th>Tên</th>
        <th>SDT</th>
        <th>Điểm</th>
        <th>Thời gian</th>
    </tr>
    </thead>
    <tbody>
    @foreach($entities as $key => $entity)
        <tr>
            <td>{{ $key+1 }}</td>
            <td>{{ data_get($entity, 'user.name') }}</td>
            <td>{{ data_get($entity, 'user.tel') }}</td>
            <td>{{ $entity->star_count }}</td>
            <td>{{ $entity->time }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
