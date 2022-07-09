# WhatsAppMusic
WhatsApp bot that send you music from YouTube by request

# WhatsAppMusic

**Created by Liam Mizrahi**.


# Technology

**Server side** written with **PHP** with as part of **Laravel framework**.
Although no Laravel is required and the script can be easy manipulated into vanilla PHP Script.
**Bridge** written with node.js, the bridging script connect between the client (WhatsApp) and the server, We use the script as the receiver of the message and the sender.
It means that this node.js script (with whatsapp-web.js library) only get messages and send them to the server, and get response from the server and send it back to the user.

## Required Libraries & Workspace

For server: any linux server that can run PHP/Laravel
For bridge: Node.js server. for example, Heroku.
Smartphone with WhatsApp. need an internet connection only for the setup. If the login method is set to Multi-Device, you can disconnect from the network after the bridge is connected and authenticated
