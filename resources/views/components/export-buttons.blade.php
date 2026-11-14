@props(['route'])
<div class="btn-group btn-group-sm">
    <a class="btn btn-outline-success" href="{{ route($route, array_merge(['format' => 'csv'], request()->query())) }}"><i class="bi bi-filetype-csv"></i> CSV</a>
    <a class="btn btn-outline-danger" href="{{ route($route, array_merge(['format' => 'pdf'], request()->query())) }}"><i class="bi bi-filetype-pdf"></i> PDF</a>
</div>
