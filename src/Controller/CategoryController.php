<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryFilterType;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{
    /**
     * @Route("/category", name="categoryList", methods={"GET", "POST"})
     */
    public function index( CategoryRepository $categoryRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $formFilter = $this->createForm(CategoryFilterType::class);
        $formFilter->handleRequest($request);
        $filter = [];
        if ($formFilter->isSubmitted() && $formFilter->isValid()) {
            $filter = $formFilter->getData();
        }
        $category=$categoryRepository->getcategoryFilters($filter);
        $category = $paginator->paginate(
            $category, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            2 /*limit per page*/
        );
        return $this->render('category/index.html.twig', [
            'categoryViews' => $category,
            'form' => $formFilter->createView(),
        ]);
    }

    /**
     * @Route("/newCategory", name="newCategory", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $category-> setCreatedAt(new \DateTime());
            $category->setStatus(true);
            $entityManager->persist($category);
            $entityManager->flush();

            $this->addFlash('success', 'Successfully added');

            return $this->redirectToRoute('categoryList');
        }

        return $this->render('product/new.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/showCategory/{id}/", name="categoryShow", methods={"GET"})
     */
    public function show(Category $category): Response
    {
        return $this->render('category/show.html.twig', [
            'category' => $category,
        ]);
    }

    /**
     * @Route("/{id}/editCategory/", name="categoryEdit", methods={"GET","POST"})
     */
    public function edit(Request $request, Category $category): Response
    {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $category-> setUpdatedAt(new \DateTime());
            $category->setStatus(true);
            $this->getDoctrine()->getManager()->flush();


            $this->addFlash('success', 'Successfully updated');

            return $this->redirectToRoute('categoryList');
        }

        return $this->render('category/edit.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/{id}", name="categoryDelete", methods={"DELETE"})
     */
    public function delete(Request $request, Category $category): Response
    {
        if ($this->isCsrfTokenValid('delete'.$category->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $category->setStatus(false);
            $category-> setUpdatedAt(new \DateTime());
            $entityManager->flush();
        }
        $this->addFlash('success', 'Successfully delete');
        return $this->redirectToRoute('categoryList');
    }


    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct( EntityManagerInterface $entityManager)
    {

        $this->entityManager = $entityManager;
    }

    private function getData(): array
    {
        /**
         * @var $category Category[]
         */
        $list = [];
        $categories = $this->entityManager->getRepository(Category::class)->findAll();

        foreach ($categories as $category) {
            $list[] = [
                $category->getName(),
                $category->getActive(),
            ];
        }
        return $list;
    }

    /**
     * @Route("/exportCategory",  name="exportCategory")
     */

    public function export(){
        $spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle('Category list');

        $sheet->getCell('A1')->setValue('Name');
        $sheet->getCell('B1')->setValue('Active');

        // Increase row cursor after header write
        $sheet->fromArray($this->getData(),null, 'A3', true);


        $writer = new Xlsx($spreadsheet);
        $fileName ='categories.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($temp_file);


        return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);

    }



}

