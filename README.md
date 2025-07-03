# Note:

Due to a new version of this site being written in JavaScript (Next.js), this project is **deprecated** and will no
longer be maintained. 

# **Color Wars**

This site is a fully-featured [LAMP](https://en.wikipedia.org/wiki/LAMP_(software_bundle)) website created to streamline
and modernize the week of *Color Wars* at a summer camp I work at. During this week, groups are put together into teams
where each team has a color. These teams then compete against each other in challenges throughout the day for points, and
at the end of the week, the team with the most points wins. It's always been a blast, but managing the points coming in
throughout camp was slow and manual, never mind displaying the points, which was done daily on boards on a field. So,
I decided to create a website that would solve both the adding and viewing of points for camp.

# Installation Instructions

### Prerequisites
You must have docker installed on the machine with root (sudo) access. I have only tested this on Ubuntu Server and
Windows, but there shouldn't be a problem with other operating systems.

### Instructions
1. Clone this repository to your machine
2. Create a `passwords.env` file in this root directory
   - Passwords need to be created for the following:
     1. ADMIN_PASSWORD
     2. DB_PASSWORD
     3. MYSQL_ROOT_PASSWORD
   - DB_PASSWORD and MYSQL_ROOT_PASSWORD should be the same
   - Refer to `.env.example` for a sample format

#### If using SSL (HTTPS):
2. Obtain SSL (HTTPS) certificates
   - You must get both the key and certificate
3. Uncomment *all* commented lines in `compose.yaml` and `Dockerfile`
   - This should be the volumes and ports 443 in the compose file and the command and port 443 in the dockerfile
4. Name the key file *privkey.pem* and the certificate file *fullchain.pem*, and place them in the main directory of the project (most likely color-wars-website)
5. In `compose.yaml`, replace both **[PASSWORD HERE]** instances with your own password.
6. In `ssl.conf`, replace **[SITE NAME HERE]** with your website domain name attached to the certificate
7. Run `docker compose up` or `docker compose up -d` to run in detached mode

#### If *not* using SSL (HTTPS):
2. In `compose.yaml`, replace both **[PASSWORD HERE]** instances with your own password.
3. Run `docker compose up` or `docker compose up -d` to run in detached mode
