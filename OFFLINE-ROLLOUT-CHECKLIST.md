# FSR offline desktop rollout checklist

Run this checklist on one test Windows computer before distributing the installer.

1. Deploy the matching FSR PHP files and `service-worker.js` to the sandbox.
2. Sign in online with a Warehouse Personnel or System Admin account.
3. In Account Settings, enable Offline Mode. Keep the desktop app open while it prepares the workspace.
4. Confirm the app asks for a six-digit Offline PIN and then shows the device in Account → Registered Devices.
5. Disconnect the computer from the internet. Restart the app and confirm that it asks for the Offline PIN.
6. Open a cached encoding form and save a test record. Confirm the red offline banner and `Not uploaded online` message appear.
7. Reconnect the computer. Keep the desktop app open and signed in. Confirm the record changes to `Uploading…` and then disappears from the pending count.
8. In User Control → Offline Sync Review, confirm the upload is listed as `Uploaded`.
9. Repeat an upload while deliberately disconnecting during the upload. Confirm it is shown as needing review instead of being submitted twice.
10. In User Control → Offline Devices, revoke the test device. Confirm its future queued records do not upload after reconnecting.

Do not distribute the installer until every test above succeeds on the sandbox.
