<table>
    <thead>
    <tr>
        <th>No</th>
        <th>Name</th>
        <th>DOB</th>
        <th>Phone</th>
        <th>Username</th>
        <th>Email</th>
        <th>Address</th>
    </tr>
    </thead>
    <tbody>
    @foreach($students as $key => $student)
        <tr>
            <td>{{ $key+1 }}</td>
            <td>{{ $student->name }}</td>
            <td>{{ \Carbon\Carbon::parse($student->birthday)->format('d/m/Y') }}</td>
            <td>{{ $student->tel }}</td>
            <td>{{ $student->username }}</td>
            <td>{{ $student->email }}</td>
            <td>{{ $student->address }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
