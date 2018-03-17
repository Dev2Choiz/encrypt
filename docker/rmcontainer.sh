#!/bin/bash

sudo docker stop $(docker ps -aq)
sudo docker rm -vf  $(docker ps -aq)
#sudo docker rmi -f $(docker images -aq)
#sudo docker volume prune -f
