@extends('layouts.app')
@section('title','Rollar we rugsatlar')
@section('content')
<div class="mx-auto max-w-7xl space-y-6">
 <header><h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">Rollar we rugsatlar</h1><p class="mt-1 text-sm text-slate-500">Her rola rugsatlary belläň. Admin diňe özünde bar rugsatlary paýlap biler.</p></header>
 @if(session('success'))<div class="rounded-xl bg-emerald-50 p-3 text-emerald-700">{{session('success')}}</div>@endif
 @if($errors->any())<div class="rounded-xl bg-red-50 p-3 text-red-700">@foreach($errors->all() as $e)<p>{{$e}}</p>@endforeach</div>@endif
 <section class="rounded-3xl border border-slate-200 bg-white/70 p-6 shadow-lg backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/70">
  <h2 class="mb-4 text-lg font-bold">Täze rol döret</h2>
  <form method="POST" action="{{route('admin.roles.store')}}" class="space-y-4">@csrf
   <label class="block text-sm font-semibold">Rol ady<input name="name" required maxlength="80" class="mt-1 w-full rounded-xl border p-3 dark:bg-slate-950" placeholder="Meselem: Klinika ýolbaşçysy"></label>
   <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">@foreach($permissions->groupBy('group') as $group=>$groupPermissions)<fieldset class="rounded-xl border border-slate-200 p-3 dark:border-slate-700"><legend class="px-1 text-sm font-bold">{{$group}}</legend>@foreach($groupPermissions as $permission)<label class="flex items-center gap-2 py-1 text-xs"><input type="checkbox" name="permissions[]" value="{{$permission->id}}">{{$permission->name}}</label>@endforeach</fieldset>@endforeach</div>
   <button class="rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white">Rol döret</button>
  </form>
 </section>
 @if(in_array(strtolower((string)auth()->user()->role), ['superadmin','super_admin']))
 <section class="rounded-3xl border border-slate-200 bg-white/70 p-6 shadow-lg dark:border-slate-800 dark:bg-slate-900/70"><h2 class="mb-3 font-bold">Täze rugsat döret (SuperAdmin)</h2><form method="POST" action="{{route('admin.permissions.store')}}" class="grid gap-3 sm:grid-cols-3">@csrf<input name="name" required placeholder="reports.custom_export" class="rounded-xl border p-3 dark:bg-slate-950"><input name="group" required placeholder="reports" class="rounded-xl border p-3 dark:bg-slate-950"><input name="description" placeholder="Düşündiriş" class="rounded-xl border p-3 dark:bg-slate-950"><button class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white sm:col-span-3">Rugsat döret</button></form></section>
 @endif
 <section class="space-y-4">@foreach($roles as $role)
  <article class="rounded-3xl border border-slate-200 bg-white/70 p-5 shadow-lg backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/70">
   <div class="flex flex-wrap items-center justify-between gap-3"><div><h3 class="font-bold">{{$role->name}}</h3><p class="text-xs text-slate-500">{{$role->slug}} · {{$role->permissions->count()}} rugsat</p></div>@if($role->is_system || in_array($role->slug,['superadmin','admin']))<span class="rounded-full bg-slate-100 px-3 py-1 text-xs dark:bg-slate-800">Goragly ulgam roly</span>@endif</div>
   @if(!$role->is_system && !in_array($role->slug,['superadmin','admin']))
   <details class="mt-4"><summary class="cursor-pointer text-sm font-semibold text-emerald-700">Roly redaktirle</summary><form method="POST" action="{{route('admin.roles.update',$role)}}" class="mt-4 space-y-4">@csrf @method('PUT')<input name="name" value="{{$role->name}}" required class="w-full rounded-xl border p-3 dark:bg-slate-950"><div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">@foreach($permissions->groupBy('group') as $group=>$groupPermissions)<fieldset class="rounded-xl border p-3 dark:border-slate-700"><legend class="px-1 text-sm font-bold">{{$group}}</legend>@foreach($groupPermissions as $permission)<label class="flex items-center gap-2 py-1 text-xs"><input type="checkbox" name="permissions[]" value="{{$permission->id}}" {{$role->permissions->contains('id',$permission->id)?'checked':''}}>{{$permission->name}}</label>@endforeach</fieldset>@endforeach</div><button class="rounded-xl bg-emerald-600 px-4 py-2 text-sm text-white">Üýtgetmeleri sakla</button></form></details>
   <form method="POST" action="{{route('admin.roles.destroy',$role)}}" class="mt-3" onsubmit="return confirm('Roly pozmak isleýärsiňizmi?')">@csrf @method('DELETE')<button class="text-xs font-semibold text-red-600">Roly poz</button></form>
   @else <div class="mt-3 flex flex-wrap gap-2">@foreach($role->permissions as $permission)<span class="rounded-lg bg-slate-100 px-2 py-1 text-xs dark:bg-slate-800">{{$permission->name}}</span>@endforeach</div>@endif
  </article>
 @endforeach</section>
</div>
@endsection
