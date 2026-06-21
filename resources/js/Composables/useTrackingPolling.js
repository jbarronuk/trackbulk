import { onMounted, onUnmounted } from 'vue'
import axios from 'axios'

/**
 * @param {object}   options
 * @param {string}   options.format   'time' | 'date'
 * @param {Function} options.range    returns { start, end } ISO strings
 * @param {Function} options.onData   receives the response payload
 * @param {number}   [options.interval=5000]
 */
export function useTrackingPolling({ format, range, onData, interval = 5000 }) {
    let timeoutId

    const poll = async () => {
        try {
            const { start, end } = range()
            const { data } = await axios.get('/api/tracking', {
                params: { format, start, end },
            })
            onData(data)
        } catch (error) {
            console.error('Error fetching tracking data:', error)
        } finally {
            timeoutId = setTimeout(poll, interval)
        }
    }

    onMounted(() => {
        timeoutId = setTimeout(poll, interval)
    })

    onUnmounted(() => {
        clearTimeout(timeoutId)
    })
}
