<form action="{{ route('import.excel') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="file" name="excel_data" required>
    <button type="submit">Upload</button>
</form>
