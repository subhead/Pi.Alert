# Pi.Alert - Device Management
<!--- --------------------------------------------------------------------- --->
To edit device information:
  - Select "Devices" in the menu on the left of the screen
  - Find the device you want to edit in the central table
  - Go to the device page by clicking on the device name or status
  - Press "Details" tab of the device
  - Edit the device data
  - Press the "Save" button


![Device Details][screen1]


## Main Info
  - **MAC**: MAC addres of the device. Not editable.
  - **Name**: Friendly device name
  - **Owner**: Device owner (The list is self-populated with existing owners)
  - **Type**: Select a device type from the dropdown list (Smartphone, Table,
      Laptop, TV, router, ....) or type a new device type
  - **Vendor**: Automatically updated by Pi.Alert when empty
  - **Model**: The model name to describe the device in more detail
  - **Serial Number**: Here you can enter any existing serial number for unique identification
  - **Group**: Select a grouper ('Always on', 'Personal', Friends') or type your own Group name
  - **Location**: Select a location ('Hall', 'Kitchen', ....) of the device
  - **Favorite**: Mark the device as favorite and then it will appears at the begining of the device list
  - **Comments**: Type any comments for the device

## Session Info
  - **Status**: Show device status : Online / Offline
  - **First Session**: Date and time of the first connection
  - **Last Session**: Date and time of the last connection
  - **Last IP**: Last known IP used during the last connection
  - **Static IP**: Check this box to identify devices that always use the
      same IP

## Network
  - **Uplink Target**: A switch, router or similar device created on the network side.
  - **Target Port Number**: The port number of this device to which the host is connected.
  - **Connection Type**: Self explanatory
  - **Link Speed**: Self explanatory


## Events & Alerts config
  - **Scan Cycle**: Select the scan cycle: 0, 1'
  - **Alert All Events**: Send a notification in each event (connection,
      disconnection, IP Changed, ...)
  - **Alert Down**: Send a notification when the device is down
    - *(Userful with "always connected" devices: Router, AP, Camera, Alexa,
      ...)*
  - **Skip repeated notifications during**: Do not send more than one
      notification to this device for X hours
    - *(Useful to avoid notification saturation on devices that frequently
      connects and disconnects)*
  - **Scan Validation**: The "Scan Validation" parameter determines how many consecutive scans 
      must detect a device as offline before its status is officially changed to "offline" or 
      "down." If a validation is pending, the device is marked as "online*" (with an asterisk as an indicator).
  - **Show on "Presence"**: The device can be hidden on the “Presence” page, 
    e.g. to increase the overview or because presence tracking is not necessary.

# Random MAC's
<!--- --------------------------------------------------------------------- --->

These **random MACs** are used for software-based network interfaces. Here, however, **Random** does not mean that they necessarily change every time, but merely that they were created **randomly**. 
Such **Random MACs** can currently be recognized by the characters "2", "6", "A", or "E" as the 2nd character in the Mac address.

The newer versions of some operating systems (IOS and Android) also have a function to improve data protection: **Random MACs**.
This functionality allows you to **hide the true MAC** of the device and **assign a random MAC** when we connect to WIFI networks.

This behavior is especially useful when connecting to WIFI's that we do not know, but it **is totally useless when connecting to our own WIFI's** or known networks.

**I recommend disabling this operation when connecting our devices to our own WIFI's**, in this way, Pi.Alert will be able to identify the device, and it
will not identify it as a new device every so often (every time IOS or Android decides to change the MAC).

### IOS
![ios][ios]

  - [Use private Wi-Fi addresses in iOS 14](https://support.apple.com/en-us/HT211227)

### Android
![Android][Android]

  - [How to Disable MAC Randomization in Android 10](https://support.boingo.com/s/article/How-to-Disable-MAC-Randomization-in-Android-10-Android-Q)
  - [How do I disable random Wi-Fi MAC address on Android 10](https://support.plume.com/hc/en-gb/articles/360052070714-How-do-I-disable-random-Wi-Fi-MAC-address-on-Android-10-)

### License
  GPL 3.0
  [Read more here](../LICENSE.txt)
  
  ***Suggestions and comments are welcome***


<!--- --------------------------------------------------------------------- --->
[main]:    https://raw.githubusercontent.com/leiweibau/Pi.Alert/assets/1_devices.jpg      "Main screen"
[screen1]: https://raw.githubusercontent.com/leiweibau/Pi.Alert/assets/screen_dev_01.png  "Screen 1"
[ios]:     https://9to5mac.com/wp-content/uploads/sites/6/2020/08/how-to-use-private-wifi-mac-address-iphone-ipad.png?resize=2048,1009 "ios"
[Android]: https://raw.githubusercontent.com/leiweibau/Pi.Alert/assets/android_random_mac.jpg  "Android"

