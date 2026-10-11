<!DOCTYPE html><html><head><meta charset="utf-8"><title>Kardex</title>
<style>body{font-family:sans-serif;font-size:12px;margin:20px}
h2{text-align:center;color:#1a56db}h3{color:#1a56db}
.header{display:flex;justify-content:space-between;margin-bottom:20px}
table{width:100%;border-collapse:collapse;margin-top:10px}
th,td{border:1px solid #ccc;padding:6px;text-align:left}
th{background:#1a56db;color:white}
.footer{position:fixed;bottom:0;width:100%;text-align:center;font-size:10px;color:#666}
</style></head><body>
<h2>SICE - Sistema Integral de Control Escolar</h2>
<h3>Kardex Académico</h3>
<div class="header"><div><strong>Alumno:</strong> {{ $alumno->user->nombre_completo }}<br><strong>Matrícula:</strong> {{ $alumno->matricula }}</div><div><strong>Especialidad:</strong> {{ $alumno->especialidad->nombre ?? 'N/A' }}<br><strong>Semestre:</strong> {{ $alumno->semestre }}</div></div>
<table><thead><tr><th>Período</th><th>Materia</th><th>P1</th><th>P2</th><th>P3</th><th>Ord.</th><th>Ext.</th><th>Promedio</th><th>Estatus</th></tr></thead><tbody>
@forelse($alumno->calificaciones as $c)
<tr><td>{{$c->periodo}}</td><td>{{$c->materia->nombre??'N/A'}}</td><td>{{$c->parcial1}}</td><td>{{$c->parcial2}}</td><td>{{$c->parcial3}}</td><td>{{$c->ordinario}}</td><td>{{$c->extraordinario}}</td><td>{{$c->promedio_final}}</td><td>{{$c->estatus}}</td></tr>
@empty
<tr><td colspan="9" style="text-align:center">Sin calificaciones registradas</td></tr>
@endforelse
</tbody></table>
<div class="footer">SICE &copy; {{ date('Y') }} - Documento generado automáticamente</div>
</body></html>
