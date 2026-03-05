@if(Session::has('message'))
    @if( 'danger' == Session::get('message_type') )
    <script>
        $(document).ready(function(){
            toastr.error
            ("{{ Session::get('message') }}");
        });
    </script>
    @elseif( 'success' == Session::get('message_type') )
    <script>
        $(document).ready(function(){
            toastr.success
            ("{{ Session::get('message') }}");
        });
    </script>
    @elseif( 'warning' == Session::get('message_type') )
    <script>
        $(document).ready(function(){
            toastr.warning
            ("{{ Session::get('message') }}");
        });
        </script>
        @elseif( 'info' == Session::get('message_type') )
    <script>
        $(document).ready(function(){
            toastr.info
            ("{{ Session::get('message') }}");
        });
    </script>
    @endif
@endif