# Wat moet ik halen?
Tool for calculating minimum required grades in order to graduate Dutch public middle school system.
For a live version visit [watmoetikhalen.nl](https://watmoetikhalen.nl).

![Screenshot of website](https://github.com/ethergeus/watmoetikhalen/blob/main/assets/screenshot.png?raw=true)

## Features
The tool allows for a student to select their education level (VMBO, HAVO or VWO) and study program. A configuration file then gives this student the option to fill in the optional subjects they're taking to completely represent their curriculum. For each subject the student can fill in any number of grades with their corresponding weight. Clicking on the plus and minus adds and removes additional fields.

![Screenshot of website](https://github.com/ethergeus/watmoetikhalen/blob/main/assets/subject.png?raw=true)

Furthermore, the student can register, log in and reset their password as they would expect from an app with persistent cloud storage. To prevent spam logins there is a reCAPTCHA challenge before conducting spam-sensitive actions. Clicking on 'forget me' at the bottom of the page deletes all grades currently viewed.

## Installation
The container can either be built, or pulled from a repository:
```bash
# Build the image yourself:
git clone https://github.com/ethergeus/watmoetikhalen.git
cd watmoetikhalen
docker build .

# Pull from Docker Hub:
docker pull ethergeus/watmoetikhalen

# Pull from GitHub container registry:
docker pull ghcr.io/ethergeus/watmoetikhalen
```

## Configuration
There is a template for the `.env` file and `.msmtprc` file, for environment variables and mail credentials called `.env-template` and `.msmtprc-template` respectively. The container uses `msmtp` to send emails via `php`. For full documentation on `msmtp` visit the [Debian Wiki](https://wiki.debian.org/msmtp).

Modify the `docker-compose.yml` configuration file to suit your needs, the provided `docker-compose.yml` assumes there's an `nginx` docker container and frontend network that handles the incoming requests on port 80 and 443.
