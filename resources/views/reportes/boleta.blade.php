<!DOCTYPE html><html><head><meta charset="utf-8"><title>Boleta</title>
<style>body{font-family:sans-serif;font-size:14px;margin:20px}
h2{text-align:center;color:#1a56db}h3{text-align:center}
.header{display:flex;justify-content:space-between;margin-bottom:20px;border-bottom:2px solid #1a56db;padding-bottom:10px}
table{width:100%;border-collapse:collapse;margin-top:15px}
th,td{border:1px solid #ccc;padding:8px;text-align:center}
th{background:#1a56db;color:white}
.summary{margin-top:20px;text-align:right}
</style></head><body>
<h2>SICE - Sistema Integral de Control Escolar</h2>
<h3>Boleta de Calificaciones</h3>
<div class="header"><div><strong>Alumno:</strong> {{ $alumno->user->nombre_completo }}<br><strong>Matrícula:</strong> {{ $alumno->matricula }}</div><div><strong>Periodo:</strong> {{ request('periodo') }}<br><strong>Fecha:</strong> {{ date('d/m/Y') }}</div></div>
<table><thead><tr><th>#</th><th>Materia</th><th>Parcial 1</th><th>Parcial 2</th><th>Parcial 3</th><th>Ordinario</th><th>Promedio</th><th>Estatus</th></tr></thead><tbody>
@php $i=1; @endphp
@forelse($calificaciones as $c)
<tr><td>{{$i++}}</td><td style="text-align:left">{{$c->materia->nombre??'N/A'}}</td><td>{{$c->parcial1}}</td><td>{{$c->parcial2}}</td><td>{{$c->parcial3}}</td><td>{{$c->ordinario}}</td><td>{{$c->promedio_final}}</td><td>{{$c->estatus}}</td></tr>
@empty
<tr><td colspan="8">Sin calificaciones en este período</td></tr>
@endforelse
</tbody></table>
<div class="summary"><p>Promedio general: <strong>{{ $calificaciones->count() > 0 ? number_format($calificaciones->avg('promedio_final'), 2) : 'N/A' }}</strong></p></div>
</body></html>
