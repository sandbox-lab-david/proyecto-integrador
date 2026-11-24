<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
@switch($icono)
    @case('requisitos')<path d="M14 3H5v18h14V8zM14 3v5h5M8 12l1 1 2-2M8 17l1 1 2-2M14 12h2M14 17h2"/>@break
    @case('adjuntar')<path d="m8 13 7-7a3 3 0 0 1 4 4l-9 9a5 5 0 0 1-7-7l10-10a2.8 2.8 0 0 1 4 4l-9 9a1.4 1.4 0 0 1-2-2l8-8"/>@break
    @case('persona')<circle cx="12" cy="7" r="4"/><path d="M4 21v-3a8 8 0 0 1 16 0v3z"/>@break
    @case('firma')<path d="m14 5 5 5M4 20l5-1L21 7l-5-5L4 14zM13 21h8"/>@break
    @case('calendario')<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M7 3v4M17 3v4M3 11h18M7 15h4M7 18h7"/>@break
    @case('informacion')<circle cx="12" cy="12" r="9"/><path d="M12 11v6M12 7h.01"/>@break
    @default<path d="M14 2H5v20h14V7zM14 2v5h5M8 11h8M8 15h8M8 18h5"/>
@endswitch
</svg>
