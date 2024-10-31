const NETWORK_STATE_CHECK_INTERVAL = 10 * 1000 // ms
const OFFLINE_DB_NAME = 'OfflineTempStorage'
const OFFLINE_DB_STORE = 'PostRequests'

let client, poller

const dbRequest = indexedDB.open(OFFLINE_DB_NAME, 1)
dbRequest.onupgradeneeded = function(event) {
const db = event.target.result
  const cache = db.createObjectStore(OFFLINE_DB_STORE, { keyPath: 'id', autoIncrement: true })
  const timeIndex = cache.createIndex("by_time", "timestamp", { unique: false })
  const subjectIndex = cache.createIndex("by_url", "url", { unique: false })
}
dbRequest.onsuccess = function() {
  db = dbRequest.result
  offLineSupport = true
}

self.addEventListener('install', function (event) {
  console.info(`Event: ${event.type}`)
  self.skipWaiting()
  startPolling()
})

self.addEventListener('activate', (event) => {
  console.info(`Event: ${event.type}`)
  self.clients.claim()
})

self.addEventListener('offline', function (event) {
  console.info(`Event: ${event.type}`)
  startPolling()
})

self.addEventListener('online', function (event) {
  console.info(`Event: ${event.type}`)
  syncOnline()
})

addEventListener("message", (event) => {
  // client ready for data
  console.info(`Event: ${event.type}`)
  readOffline()
})

self.addEventListener('fetch', function (event) {
  console.info(`Event: ${event.type}`)
  if (event.request.method === 'POST') {
    if(!navigator.onLine){
      event.respondWith((async() => {
        const body = await event.request.text()
        saveOffline({
          method: event.request.method,
          url: event.request.url,
          body: body,
        })
        const entries = new URLSearchParams(body).entries()
        let values = {}
        for(let [key, value] of entries) {
          values[key] = value
        }
        const response = new Response([values])
        return response
      })())
    }
  }
})

function logDBStatus() {
  const tx = db.transaction(OFFLINE_DB_STORE, 'readonly')
  const store = tx.objectStore(OFFLINE_DB_STORE)
  const countRequest = store.count()
  countRequest.onsuccess = () => {
    const count = countRequest.result
    console.log(`${count} row${count !=1 ? 's' : ''} in offline cache`)
  }
}

async function readOffline() {
  const tx = db.transaction(OFFLINE_DB_STORE, 'readonly')
  const store = tx.objectStore(OFFLINE_DB_STORE)
  const allRecords = store.getAll()
  allRecords.onsuccess = async function () {
    if (allRecords.result) {
      logDBStatus()
      if (allRecords.result.length < 1) {
        endPolling()
      }
      for (const record of allRecords.result) {
        const entries = new URLSearchParams(record.body).entries()
        let values = {}
        for(let [key, value] of entries) {
          values[key] = value
        }
        self.clients.matchAll().then((clientList) => {
          // the values will be sent to all browser tabs
          for (const client of clientList) {
            client.postMessage([values])
          }
        })
      }
    }
  }
}

function startPolling() {
  if (!poller) {
    console.log('Started polling for network state')
    poller = setInterval(syncOnline, NETWORK_STATE_CHECK_INTERVAL)
  }
}

function endPolling() {
  console.log('Stopping network state polling')
  clearInterval(poller)
}

function saveOffline(obj) {
  obj.timestamp = new Date().toISOString()
  const tx = db.transaction(OFFLINE_DB_STORE, 'readwrite')
  const store = tx.objectStore(OFFLINE_DB_STORE)
  store.add(obj)
  logDBStatus()
  startPolling()
}

async function syncOnline() {
  if (!navigator.onLine) return false
  const tx = db.transaction(OFFLINE_DB_STORE, 'readonly')
  const store = tx.objectStore(OFFLINE_DB_STORE)
  const allRecords = store.getAll()
  allRecords.onsuccess = async function () {
    if (allRecords.result) {
      logDBStatus()
      if (allRecords.result.length < 1) {
        endPolling()
      }
      for (const record of allRecords.result) {
        if (!navigator.onLine) break
        await fetch(record.url, {
          method: record.method,
          headers: {'Content-Type': 'application/x-www-form-urlencoded'},
          body: new URLSearchParams(record.body)
        }).then(async (resp) => {
/*
          const values = await resp.json()
          self.clients.matchAll().then((clientList) => {
            // the values will be sent to all browser tabs
            for (const client of clientList) {
              client.postMessage(values)
            }
          })
*/
          const updateTx = db.transaction(OFFLINE_DB_STORE, 'readwrite')
          const updateStore = updateTx.objectStore(OFFLINE_DB_STORE)
          updateStore.delete(record.id)
        }).catch((e) => {
          console.error(e)
        }).finally((e) => {
          logDBStatus()
        })
      }
    }
  }
}
