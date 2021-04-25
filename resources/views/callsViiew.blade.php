<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Laravel Ajax File Upload Example - Tutsmake.com</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <style>
        .container{
            padding: 0.5%;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-sm bg-primary navbar-dark">
    <a class="navbar-brand" href="{{ url('/') }}">CommPeak Task</a>
</nav>
<div class="container mt-4">
    <div class="row">
        <div class="col-sm-12">
            <h4>Upload list of customer</h4>
        </div>
    </div>
    <form method="POST" enctype="multipart/form-data" id="file-upload" action="javascript:void(0)" >
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <input type="file" name="file" placeholder="Choose File" id="file">
                    <span class="text-danger">{{ $errors->first('file') }}</span>
                </div>
            </div>
            <div class="col-md-12">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </div>
    </form>
    <div class="row"><br><br><br></div>
    <div class="row" id="loadingim">
        <div style="text-align: center">
            <div class="spinner-grow text-primary" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <div class="spinner-grow text-secondary" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <div class="spinner-grow text-success" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <div class="spinner-grow text-danger" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <div class="spinner-grow text-warning" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <div class="spinner-grow text-info" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <div class="spinner-grow text-light" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <div class="spinner-grow text-dark" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
    </div>
    <div class="row" id="table"><br><br><br></div>
</div>

</body>
<script type="text/javascript">
    $(document).ready(function (e) {
        $('#loadingim').hide();
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $('#file-upload').submit(function(e) {
            e.preventDefault();
            $('#loadingim').show();
            var formData = new FormData(this);
            $.ajax({
                type:'POST',
                url: "{{ url('store-file')}}",
                data: formData,
                cache:false,
                contentType: false,
                processData: false,
                success: function(data) {
                    $('#loadingim').hide();
                    $('#file').val('');
                    $('#table').html(data.table);
                },
                error: function(data){
                    console.log(data);
                }
            });
        });
    });
</script>
</html>
