<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File;

use App\Service\ImgUploader;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(): Response
    {
        $em = $this->getDoctrine()->getManager();

        /** @var PostRepository $postRepository */
        $postRepository = $em->getRepository(Post::class);

        $posts = $postRepository->findAll();

        return $this->render('home/index.html.twig', [
            'posts' => $posts
        ]);
    }

    /**
     * @Route("/create", name="create_post")
     * @param Request $request
     * @param ImgUploader $ImgUploader
     * @return Response
     */
    public function create(Request $request, ImgUploader $ImgUploader): Response
    {
        $newPost = new Post();

        $form = $this->createForm(PostType::class, $newPost);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $newPost->setDate(new \DateTime('now'));

            /** @var File\UploadedFile $file */
            $file = $form->get('img')->getData();

            $fileName = $ImgUploader->upload($file);

            $newPost->setImg($fileName);

            $em = $this->getDoctrine()->getManager();
            $em->persist($newPost);
            $em->flush();

            return $this->redirectToRoute('home');
        }

        return $this->render('post/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/delete/{id}", name="delete")
     * @param $id
     * @return Response
     */
    public function delete($id): Response
    {
        $em = $this->getDoctrine()->getManager();

        /** @var PostRepository $postRepository */
        $postRepository = $em->getRepository(Post::class);

        $posts = $postRepository->findOneBy(['id' => $id]);
        if ($posts) {

            unlink('img/' . $posts->getImg());
            $em->remove($posts);
            $em->flush();

            return $this->redirectToRoute('home');
        }
    }

    /**
     * @Route("/edit/{id}", name="update")
     * @param Request $request
     * @param $id
     * @param ImgUploader $ImgUploader
     * @return Response
     */
    public function update(Request $request, $id, ImgUploader $ImgUploader): Response
    {
        $em = $this->getDoctrine()->getManager();

        /** @var PostRepository $postRepository */
        $postRepository = $em->getRepository(Post::class);

        $posts = $postRepository->findOneBy(['id' => $id]);

        if (!$posts) {
            throw $this->createNotFoundException(
                'No book found for id ' . $id
            );
        }

        $img = $posts->getImg();
        $form = $this->createForm(PostType::class, $posts);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();

            if ($data->getImg() == null) {

                $data->setImg($img);
                $em->flush();

                return $this->redirectToRoute('home');
            } else {
                /** @var File\UploadedFile $file */
                $file = $form->get('img')->getData();

                $fileName = $ImgUploader->upload($file);

                $data->setImg($fileName);

                unlink('img/' . $img);
                $em->flush();

                return $this->redirectToRoute('home');
            }

        }

        return $this->render('post/update.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/show/{id}", name="show")
     * @param $id
     * @return Response
     */
    public function show($id): Response
    {
        $em = $this->getDoctrine()->getManager();

        $getImg =
        /** @var PostRepository $postRepository */
        $postRepository = $em->getRepository(Post::class);

        $post = $postRepository->findOneBy(['id' => $id]);

        if (!$post) {
            throw $this->createNotFoundException(
                'No book found for id ' . $id
            );
        }
        return $this->render('post/show.html.twig', [
            'post' => $post
        ]);
    }
}
