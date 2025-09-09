@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Laravel')
<span style="font-size: 24px; font-weight: bold; color: #333;">{{ config('app.name', 'NGO Platform') }}</span>
@else
{!! $slot !!}
@endif
</a>
</td>
</tr>
