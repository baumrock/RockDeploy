# RockDeploy

Set secrets `SSH_USER` and `SSH_HOST`

    SSH_USER = example
    SSH_HOST = your.server.com

Create a keypair for your deploy workflow:

    ssh-keygen -t rsa -b 4096 -C "deploy-[project]@rockdeploy.pw" -f ~/.ssh/id_rockdeploy

Copy content of the private key to your git secret `SSH_KEY`:

    cat ~/.ssh/id_rockdeploy

Copy content of keyscan to your git secret `KNOWN_HOSTS`

    ssh-keyscan your.server.com

Add the public key to your remote user:

    ssh-copy-id -i ~/.ssh/id_rockdeploy user@your.server.com
