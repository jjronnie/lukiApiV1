@extends('layouts.admin')

@section('content')
<h1>Pricing Rule</h1>
<pre>{{ json_encode($rule->toArray(), JSON_PRETTY_PRINT) }}</pre>
@endsection
