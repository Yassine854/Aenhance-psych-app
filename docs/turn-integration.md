# Consultation TURN integration

Both consultation pages use `VideoCallPanel.vue`. Laravel creates the appointment's stable session room UUID and tracks presence; the psychologist initiates offers. The existing WebSocket server relays offers, answers and trickled ICE candidates. These flows are unchanged.

## Server environment

Set `TURN_SHARED_SECRET` privately in Laravel's environment to the exact existing Coturn `static-auth-secret`. Coturn must use `use-auth-secret` (confirmed by the operator). Never put this secret in a `VITE_` variable. No production environment or Coturn configuration was edited.

The old `VITE_TURN_USERNAME`, `VITE_TURN_CREDENTIAL`, `VITE_TURN_API_KEY`, `VITE_TURN_URL`, and `VITE_TURN_CREDENTIALS_URL` are no longer consumed by the panel. Remove sensitive values from these public variables before rebuilding. Rotate any long-lived secrets previously shipped in frontend bundles or logs. A provider API key or fixed TURN password is not interchangeable with Coturn's shared secret.

Use the existing signaling setting `VITE_SIGNALING_URL=wss://signaling.staging.aenhance.net`; signaling behavior was preserved. Refresh Laravel's configuration cache according to your deployment procedure after setting the server secret, then rebuild the frontend.

The authenticated, throttled `GET /appointments/{appointment}/turn-credentials` endpoint permits only participants of confirmed appointments. It returns noncacheable credentials valid for two hours: `expiry:userId` and Base64 HMAC-SHA1 signed with the private shared secret. The browser receives only temporary credentials. Both roles fetch them when creating a peer connection. STUN remains available and normal ICE policy is `all`. The panel now refreshes expiring credentials and attempts an ICE restart before expiry; see call-recovery.md. Renewal requires a reachable credential endpoint and still needs a long-call test against deployed Coturn.

The supplied public IP is used rather than the VPS hostname, which need not resolve publicly. The UDP/TCP URLs use port 3478. The relay range 49160–49260 belongs in Coturn/firewall configuration, not browser ICE configuration. Verify the existing VPS and network firewalls permit 3478 UDP/TCP and the configured relay range; this implementation does not change them.

## Validation on deployed devices

1. Use a confirmed appointment with its assigned psychologist and patient. Use HTTPS and grant camera/microphone access.
2. Join on two devices on different networks, for example home Wi-Fi and cellular data. Test each join order and bidirectional audio/video, mute, camera toggles, reconnect and hangup.
3. In Network tools, verify the credential endpoint succeeds on both devices. Do not copy its response into logs or tickets. Check ICE gathering/connection state logs and ICE server error codes. A credential response or gathered relay candidate alone does not prove relay media works.
4. Normal connections may select a direct path. To require relay for a controlled debugging build, set the existing `VITE_FORCE_RELAY=1`, rebuild, and rejoin on both devices. Remove it and rebuild after testing. Keep it unset or `0` for normal production.
5. Open `chrome://webrtc-internals` (Chrome), `edge://webrtc-internals` (Edge), or `about:webrtc` (Firefox) before joining. Inspect the selected candidate pair and verify media byte counters increase in both directions. Treat exported diagnostics as sensitive.

With access to the peer object in a DevTools breakpoint inside `refreshIceModeFromStats(peer)`, run this in an async console context:

```js
const stats = await peer.getStats()
const transport = [...stats.values()].find(r => r.type === 'transport' && r.selectedCandidatePairId)
const pair = transport
  ? stats.get(transport.selectedCandidatePairId)
  : [...stats.values()].find(r => r.type === 'candidate-pair' && r.state === 'succeeded' && (r.selected || r.nominated))
const local = stats.get(pair?.localCandidateId)
const remote = stats.get(pair?.remoteCandidateId)
console.table({
  pair: { state: pair?.state, bytesSent: pair?.bytesSent, bytesReceived: pair?.bytesReceived },
  local: { type: local?.candidateType, protocol: local?.protocol, relayProtocol: local?.relayProtocol },
  remote: { type: remote?.candidateType, protocol: remote?.protocol },
})
```

Either selected candidate having `candidateType: 'relay'` confirms that the selected path includes TURN. Increasing media counters plus working audio/video confirm traffic is flowing. The panel's existing ICE badge now recognizes a relay on either side. Actual relay connectivity requires the deployed two-device test; local automated checks cannot establish it.

Coturn authentication reference: https://github.com/coturn/coturn/blob/master/README.turnserver
