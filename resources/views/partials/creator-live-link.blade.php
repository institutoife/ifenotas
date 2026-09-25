<style>
.creator-link{display:inline-flex;width:max-content;max-width:100%;align-items:center;gap:9px;min-height:44px;margin-bottom:20px;border:1px solid #fff;border-radius:999px;background:#fff;padding:8px 15px;color:#17171b;text-decoration:none;font-size:.84rem;font-weight:900;box-shadow:0 8px 24px rgba(0,0,0,.18);transition:.16s}.creator-link:hover{transform:translateY(-2px);box-shadow:0 11px 28px rgba(0,0,0,.24)}.creator-link:active{transform:scale(.98)}.creator-link:focus-visible{outline:4px solid rgba(38,186,165,.4);outline-offset:3px}.creator-link i{font-size:1.15rem}
        .creator-link{gap:12px;padding:7px 15px 7px 9px;min-height:70px}.creator-link>span:not(.tiktok-live-avatar){min-width:0}.tiktok-live-avatar{position:relative;display:grid;place-items:center;flex:0 0 52px;width:52px;height:52px;border:2px solid #ff1768;border-radius:50%;background:#fff;box-shadow:0 0 0 2px #fff;animation:tiktok-live-pan 3.2s ease-in-out infinite}.tiktok-live-avatar::before{content:"";position:absolute;inset:-5px;border:1px solid #ff3785;border-radius:50%;animation:tiktok-live-ring 1.8s ease-out infinite;pointer-events:none}.tiktok-live-picture{display:grid;place-items:center;width:44px;height:44px;overflow:hidden;border-radius:50%;background:#fff}.tiktok-live-picture img{display:block;width:32px;height:35px;object-fit:contain;animation:tiktok-live-zoom 3.2s ease-in-out infinite}.tiktok-live-badge{position:absolute;z-index:1;top:-3px;left:50%;transform:translateX(-50%);border:1px solid #fff;border-radius:4px;background:#ff1768;padding:1px 5px;color:#fff;font-size:9px;font-weight:900;line-height:1.2;letter-spacing:.02em}.creator-link>.fa-tiktok{flex-shrink:0}
        @keyframes tiktok-live-pan{0%,100%{transform:translate(-1px,1px) scale(.96)}50%{transform:translate(1px,-1px) scale(1.04)}}
        @keyframes tiktok-live-zoom{0%,100%{transform:translate(-1px,1px) scale(1)}50%{transform:translate(1px,-1px) scale(1.1)}}
        @keyframes tiktok-live-ring{0%{transform:scale(.96);opacity:.8}100%{transform:scale(1.15);opacity:0}}

.creator-link{line-height:1.45;box-sizing:border-box}.creator-link *,.creator-link *::before{box-sizing:border-box}
@media(max-width:620px){.creator-link{font-size:.78rem;margin-bottom:16px}}
@media(prefers-reduced-motion:reduce){.creator-link,.creator-link *,.creator-link *::before{animation:none!important;transition:none!important}}
</style>
<a class="creator-link" href="{{ config('ife.social.tiktok') }}" target="_blank" rel="noopener">
                        <span class="tiktok-live-avatar" aria-hidden="true">
                            <span class="tiktok-live-picture"><img src="{{ asset('images/icono-ife-educabol-instituto-formacion-educabol.svg') }}" alt="" width="32" height="35"></span>
                            <span class="tiktok-live-badge">LIVE</span>
                        </span>
                        <span>VER EN TIKTOK</span><span aria-hidden="true">↗</span>
                        
                    </a>
