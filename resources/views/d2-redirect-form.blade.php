<form id="dibsForm" action="{{ $url }}" method="{{ $method }}">
    @foreach($params as $key => $value)
        <input type="hidden" name="{{ $key }}" value="{{ $value }}"> <br/>
    @endforeach;
    <script type="text/javascript">
        document.getElementById("dibsForm").submit();
    </script>
</form>
