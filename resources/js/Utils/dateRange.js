export const startOfToday = () => new Date(new Date().setHours(0, 0, 0, 0)).toISOString()

export const endOfToday = () => new Date(new Date().setHours(23, 59, 59, 999)).toISOString()

export const epochStart = () => new Date(0).toISOString()
