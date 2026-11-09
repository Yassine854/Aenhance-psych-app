// Use preferences so devices can negotiate supported capture settings.
export function callAudioConstraints() {
  return {
    echoCancellation: { ideal: true },
    noiseSuppression: { ideal: true },
    autoGainControl: { ideal: true },
    channelCount: { ideal: 1 },
  }
}

export function callVideoConstraints() {
  return {
    width: { ideal: 1280, max: 1280 },
    height: { ideal: 720, max: 720 },
    frameRate: { ideal: 24, max: 30 },
    facingMode: { ideal: 'user' },
  }
}

export async function configureCallVideoSender(sender) {
  if (!sender?.track || sender.track.kind !== 'video' || !sender.getParameters || !sender.setParameters) return false
  try {
    const parameters = sender.getParameters()
    // Encodings are created by the browser; never invent or replace them.
    if (!parameters.encodings?.length) return false
    for (const encoding of parameters.encodings) {
      encoding.maxBitrate = 1200000
      encoding.maxFramerate = 30
    }
    // This is a ceiling. Browser congestion control can reduce bitrate,
    // resolution and frame rate further without renegotiating the call.
    await sender.setParameters(parameters)
    return true
  } catch {
    // Unsupported sender controls must never prevent a call from starting.
    return false
  }
}
